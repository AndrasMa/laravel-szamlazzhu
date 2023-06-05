<?php

namespace Omisai\SzamlazzhuAgent;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
class CookieHandler
{
    public const COOKIE_FILE_PATH = 'cookies/cookie';

    public const COOKIE_HEADER_TEXT = 'JSESSIONID=';

    public const DEFAULT_COOKIE_JSON_CONTENT = '{}';

    public const COOKIE_HANDLE_MODE_TEXT = 0;

    public const COOKIE_HANDLE_MODE_JSON = 1;

    public const COOKIE_HANDLE_MODE_DATABASE = 2;

    private SzamlaAgent $agent;

    private string $cookieIdentifier;

    private array $sessions = [];

    private string $cookieSessionId = '';

    private int $cookieHandleMode = self::COOKIE_HANDLE_MODE_JSON;

    private string $cookieFilePath = '';

    public function __construct(SzamlaAgent $agent)
    {
        $this->agent = $agent;
        $this->cookieIdentifier = $this->createCookieIdentifier();
        $this->cookieFilePath = $this->createCookieFileName();
    }

    private function addSession(array|null $sessionId): void
    {
        if (SzamlaAgentUtil::isNotNull($sessionId)) {
            $this->sessions[$this->cookieIdentifier]['sessionID'] = $sessionId;
            $this->sessions[$this->cookieIdentifier]['timestamp'] = time();
        }
    }

    public function handleSessionId($header): void
    {
        $savedSessionId = [];
        preg_match_all('/(?<=JSESSIONID=)(.*?)(?=;)/', $header, $savedSessionId);

        if (isset($savedSessionId[0][0])) {
            $this->setCookieSessionId($savedSessionId[0][0]);
            if ($this->isHandleModeJson()) {
                $this->addSession($savedSessionId[0][0]);
            }
        }
    }

    public function saveSessions(): void
    {
        if ($this->isHandleModeJson()) {
            Storage::disk('payment')->put($this->cookieFilePath, json_encode($this->sessions));
        }
    }

    public function addCookieToHeader(): void
    {
        $this->refreshJsonSessionData();
        if (!empty($this->cookieSessionId)) {
            $this->agent->addCustomHTTPHeader('Cookie', self::COOKIE_HEADER_TEXT.$this->cookieSessionId);
        }
    }

    private function createCookieIdentifier(): ?string
    {
        $username = $this->agent->getUsername();
        $apiKey = $this->agent->getApiKey();
        $result = null;

        if (!empty($username)) {
            $result = hash('sha1', $username);

        } elseif (!empty($apiKey)) {
            $result = hash('sha1', $apiKey);
        }

        if (!$result || !SzamlaAgentUtil::isNotNull($result)) {
            Log::channel('szamlazzhu')->warning('Generation of the cookie identifier is failed');
        }

        return $result;
    }

    private function initJsonSessionId(): void
    {
        $cookieFileContent = $this->getCookieFile();
        $this->checkFileIsValidJson($cookieFileContent);
        $this->sessions = json_decode($cookieFileContent, true);
    }

    private function checkFileIsValidJson(string $cookieFileContent): void
    {
        try {
            SzamlaAgentUtil::isValidJSON($cookieFileContent);
        } catch (SzamlaAgentException $e) {
            Log::channel('szamlazzhu')->error('Cookie file content is not valid for being JSON type');
            Storage::disk('payment')->put($this->cookieFilePath, self::DEFAULT_COOKIE_JSON_CONTENT);
        }
    }

    public function isHandleModeText(): bool
    {
        return $this->cookieHandleMode == self::COOKIE_HANDLE_MODE_TEXT;
    }

    public function isHandleModeJson(): bool
    {
        return $this->cookieHandleMode == self::COOKIE_HANDLE_MODE_JSON;
    }

    public function isHandleModeDatabase(): bool
    {
        return $this->cookieHandleMode == self::COOKIE_HANDLE_MODE_DATABASE;
    }

    public function getCookieHandleMode(): int
    {
        return $this->cookieHandleMode;
    }

    public function setCookieHandleMode(int $cookieHandleMode): void
    {
        $this->cookieHandleMode = $cookieHandleMode;
    }

    public function getCookieSessionId(): string
    {
        return $this->cookieSessionId;
    }

    public function setCookieSessionId(string $cookieSessionId): void
    {
        $this->cookieSessionId = $cookieSessionId;
    }

    private function refreshJsonSessionData(): void
    {
        if ($this->isHandleModeJson()) {
            $this->initJsonSessionId();
            if (isset($this->sessions[$this->cookieIdentifier])) {
                $this->cookieSessionId = $this->sessions[$this->cookieIdentifier]['sessionID'];
            }
        }
    }

    public function createCookieFileName(): string
    {
        if ($this->isHandleModeText()) {
            return sprintf('%s.text', self::COOKIE_FILE_PATH);
        }

        if ($this->isHandleModeJson()) {
            return sprintf('%s.json', self::COOKIE_FILE_PATH);
        }

        return '';
    }

    public function getCookieFilePath(): string
    {
        if (SzamlaAgentUtil::isBlank($this->cookieFilePath)) {
            if ($this->isHandleModeDatabase()) {
                Log::channel('szamlazzhu')->warning('The Cookie handle mode is "database", you cannot access the cookie from file.');
            } else {
                throw new SzamlaAgentException('No file path set in CookieHandler.');
            }
        }


        return $this->cookieFilePath;
    }

    /**
     * @throws SzamlaAgentException
     */
    public function getCookieFile(): string
    {
        if ($this->isHandleModeDatabase()) {
            throw new SzamlaAgentException('The Cookie handle mode is "database", please override the CookeHandler::getCookieFile() method.');
        }

        $cookieFile = Storage::disk('payment')->get($this->cookieFilePath);
        if (!$cookieFile) {

            throw new SzamlaAgentException('The Cookie handle mode is "database", please override the CookeHandler::getCookieFile() method.');
        }

        return $cookieFile;
    }


    public function checkCookieFile()
    {
        if (Storage::disk('payment')->exists($this->getCookieFilePath()) && Storage::disk('payment')->size($this->getCookieFilePath()) > 0 && !str_contains($this->getCookieFile(), 'curl')) {
            Storage::disk('payment')->put($this->cookieFilePath, '');
            Log::channel('szamlazzhu')->debug('The cookie file content changed');
        }
    }

    public function isUsableCookieFile()
    {
        return Storage::disk('payment')->exists($this->getCookieFilePath()) && Storage::disk('payment')->size($this->getCookieFilePath()) > 0;
    }
}
