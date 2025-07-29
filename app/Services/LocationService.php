<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class LocationService
{
    public function convertCodesToNames($data)
    {
        $result = $data;
        
        if (!empty($data['region'])) {
            $result['regionName'] = $this->getLocationName('regions', $data['region']);
        }
        
        if (!empty($data['province'])) {
            $result['provinceName'] = $this->getLocationName('provinces', $data['province']);
        }
        
        if (!empty($data['city'])) {
            $result['cityName'] = $this->getLocationName('cities-municipalities', $data['city']);
        }
        
        return $result;
    }
    
    private function getLocationName($type, $code)
    {
        $cacheKey = "location_{$type}_{$code}";
        
        return Cache::remember($cacheKey, 86400, function () use ($type, $code) {
            try {
                $response = Http::timeout(10)->get("https://psgc.gitlab.io/api/{$type}/{$code}");
                
                if ($response->successful()) {
                    return $response->json()['name'] ?? $code;
                }
                
                return $code;
            } catch (\Exception $e) {
                \Log::warning("Failed to fetch location name for {$type}/{$code}: " . $e->getMessage());
                return $code;
            }
        });
    }
}