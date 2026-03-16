<?php

namespace App\Service\Tournify;

final class TournifyClient
{
    private const API_BASE = 'https://firestore.googleapis.com/v1/projects/tournamentsoftware-a1b3d/databases/(default)/documents';
    private const API_KEY = 'AIzaSyDpqIP2yOZBWjAcknp1szptkyh0fk6zGQI';
    private const AUTH_BASE = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp';

    private ?string $idToken = null;

    /**
     * @return array{id: string, path: string, fields: array<string, mixed>}
     */
    public function findTournamentByLiveLink(string $liveLink): array
    {
        $payload = [
            'structuredQuery' => [
                'from' => [['collectionId' => 'tournaments']],
                'where' => [
                    'fieldFilter' => [
                        'field' => ['fieldPath' => 'liveLink'],
                        'op' => 'EQUAL',
                        'value' => ['stringValue' => mb_strtolower($liveLink)],
                    ],
                ],
                'limit' => 1,
            ],
        ];

        $response = $this->requestJson(':runQuery', [
            'method' => 'POST',
            'body' => json_encode($payload, JSON_THROW_ON_ERROR),
            'headers' => [
                'Content-Type: application/json',
            ],
        ]);

        if (!is_array($response) || !isset($response[0]['document']['name'], $response[0]['document']['fields'])) {
            throw new \RuntimeException(sprintf('Tournoi Tournify introuvable pour le liveLink "%s".', $liveLink));
        }

        $document = $response[0]['document'];

        return [
            'id' => basename((string) $document['name']),
            'path' => $this->documentPathFromAbsoluteName((string) $document['name']),
            'fields' => $this->decodeMap($document['fields']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listDocuments(string $documentPath, string $collectionId, int $pageSize = 200): array
    {
        $documents = [];
        $pageToken = null;

        do {
            $query = [
                'key' => self::API_KEY,
                'pageSize' => $pageSize,
            ];

            if (null !== $pageToken) {
                $query['pageToken'] = $pageToken;
            }

            $url = sprintf('/%s/%s?%s', ltrim($documentPath, '/'), $collectionId, http_build_query($query));
            $response = $this->requestJson($url);

            foreach (($response['documents'] ?? []) as $document) {
                if (!isset($document['name'], $document['fields']) || !is_array($document['fields'])) {
                    continue;
                }

                $documents[] = [
                    'id' => basename((string) $document['name']),
                    ...$this->decodeMap($document['fields']),
                ];
            }

            $pageToken = $response['nextPageToken'] ?? null;
        } while (is_string($pageToken) && '' !== $pageToken);

        return $documents;
    }

    /**
     * @return array<string, mixed>
     */
    private function requestJson(string $path, array $options = []): array
    {
        $method = $options['method'] ?? 'GET';
        $headers = $options['headers'] ?? [];
        $body = $options['body'] ?? null;

        $headers[] = 'Authorization: Bearer '.$this->getIdToken();

        $url = str_starts_with($path, 'http') ? $path : self::API_BASE.$path;
        if (!str_contains($url, 'key=')) {
            $url .= (str_contains($url, '?') ? '&' : '?').'key='.self::API_KEY;
        }

        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $body,
                'ignore_errors' => true,
                'timeout' => 20,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if (false === $response) {
            throw new \RuntimeException(sprintf('Impossible de contacter Tournify (%s).', $url));
        }

        /** @var list<string> $http_response_header */
        $http_response_header = $http_response_header ?? [];
        $statusLine = $http_response_header[0] ?? '';
        if (!preg_match('/\s(\d{3})\s/', $statusLine, $matches)) {
            throw new \RuntimeException(sprintf('Réponse HTTP Tournify invalide (%s).', $statusLine));
        }

        $statusCode = (int) $matches[1];
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \RuntimeException(sprintf('Tournify a répondu %d: %s', $statusCode, $response));
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Réponse JSON Tournify invalide.');
        }

        return $decoded;
    }

    private function getIdToken(): string
    {
        if (null !== $this->idToken) {
            return $this->idToken;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode(['returnSecureToken' => true], JSON_THROW_ON_ERROR),
                'ignore_errors' => true,
                'timeout' => 20,
            ],
        ]);

        $response = @file_get_contents(self::AUTH_BASE.'?key='.self::API_KEY, false, $context);
        if (false === $response) {
            throw new \RuntimeException('Impossible de récupérer un jeton anonyme Firebase pour Tournify.');
        }

        /** @var list<string> $http_response_header */
        $http_response_header = $http_response_header ?? [];
        $statusLine = $http_response_header[0] ?? '';
        if (!preg_match('/\s(\d{3})\s/', $statusLine, $matches)) {
            throw new \RuntimeException(sprintf('Réponse Firebase Auth invalide (%s).', $statusLine));
        }

        $statusCode = (int) $matches[1];
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \RuntimeException(sprintf('Firebase Auth a répondu %d: %s', $statusCode, $response));
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || !isset($decoded['idToken']) || !is_string($decoded['idToken']) || '' === $decoded['idToken']) {
            throw new \RuntimeException('Jeton Firebase Auth invalide pour Tournify.');
        }

        $this->idToken = $decoded['idToken'];

        return $this->idToken;
    }

    private function documentPathFromAbsoluteName(string $absoluteName): string
    {
        $prefix = 'projects/tournamentsoftware-a1b3d/databases/(default)/documents/';

        return str_starts_with($absoluteName, $prefix) ? substr($absoluteName, strlen($prefix)) : $absoluteName;
    }

    /**
     * @param array<string, mixed> $map
     *
     * @return array<string, mixed>
     */
    private function decodeMap(array $map): array
    {
        $decoded = [];

        foreach ($map as $key => $value) {
            $decoded[$key] = $this->decodeValue($value);
        }

        return $decoded;
    }

    private function decodeValue(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        if (array_key_exists('stringValue', $value)) {
            return $value['stringValue'];
        }

        if (array_key_exists('integerValue', $value)) {
            return (int) $value['integerValue'];
        }

        if (array_key_exists('doubleValue', $value)) {
            return (float) $value['doubleValue'];
        }

        if (array_key_exists('booleanValue', $value)) {
            return (bool) $value['booleanValue'];
        }

        if (array_key_exists('nullValue', $value)) {
            return null;
        }

        if (array_key_exists('arrayValue', $value)) {
            $items = $value['arrayValue']['values'] ?? [];

            return array_map(fn (mixed $item): mixed => $this->decodeValue($item), $items);
        }

        if (array_key_exists('mapValue', $value)) {
            return $this->decodeMap($value['mapValue']['fields'] ?? []);
        }

        return $value;
    }
}
