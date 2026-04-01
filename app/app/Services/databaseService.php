<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class databaseService
{
    protected Client $client;
    protected string $url;
    protected array $headers;

    public function __construct()
    {
        $this->url = config('supabase.supabase_url');

        $this->headers = [
            'apikey' => config('supabase.supabase_key'),
            'Authorization' => 'Bearer' . config('supabase.supabase_key'),
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation'
        ];

        $this->client = new Client([
            'base_uri' => $this->url,
            'headers' => $this->headers
        ]);
    }

    // GetAllData
    public function getAllData(string $table, array $query = []): array
    {
        try {
            $response = $this->client->get($table, ['query' => $query]);
            return json_decode($response->getBody()->getContents(), true);
        }

        catch (GuzzleException $error){
            return ['status' => 'error', 'message' => $error->getMessage()];
        }
    }

    // Get Data By Something
    public function getDataBy(string $table, string $column, mixed $value): array
    {
        try {
            $response = $this->client->get($table, ['query' => [$column => 'eq' . $value]]);
            return json_decode($response->getBody()->getContents(), true);
        }

        catch (GuzzleException $error){
            return ['status' => 'error', 'message' => $error->getMessage()];
        }
    }

    // Insert Data
    public function insertData(string $table, array $data): array
    {
        try {
            $response = $this->client->post($table, ['json' => $data]);
            return json_decode($response->getBody()->getContents(), true);
        }

        catch (GuzzleException $error){
            return ['status' => 'error', 'message' => $error->getMessage()];
        }
    }

    // Update Data by kolom
    public function updateDataBy(string $table, string $column, mixed $value, array $data): array
    {
        try {
            $response = $this->client->patch($table, ['query' => [$column => 'eq' . $value], 'json' => $data]);
            return json_decode($response->getBody()->getContents(), true);
        }

        catch (GuzzleException $error){
            return ['status' => 'error', 'message' => $error->getMessage()];
        }
    }

    // Delete Data by kolom
    public function deleteDataBy(string $table, string $column, mixed $value): array
    {
        try {
            $response = $this->client->delete($table, ['query' => [$column => 'eq' . $value]]);
            return json_decode($response->getBody()->getContents(), true);
        }

        catch (GuzzleException $error){
            return ['status' => 'error', 'message' => $error->getMessage()];
        }
    }   
    
    // Text Connection Only
    public function testConnection(): array 
    {
        try {
            $response = $this->client->get('', ['query' => ['limit => 1']]);
            return ['status' => 'success', 'code' => $response->getStatusCode()];
        }

        catch (GuzzleException $error){
            return ['status' => 'error', 'message' => $error->getMessage()];
        }
    }
}

?>