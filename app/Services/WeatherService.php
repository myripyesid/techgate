<?php
// Consumo de un servicio de TERCERO: clima actual de Medellin mediante la API
// publica y gratuita Open-Meteo (no requiere API key). El resultado se cachea
// unos minutos para no llamar al servicio en cada peticion y se degrada con
// elegancia (devuelve null) si el servicio no responde.

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    // Coordenadas de Medellin, Colombia.
    private const LAT = 6.25;
    private const LON = -75.56;

    public function medellin(): ?array
    {
        return Cache::remember('clima_medellin', now()->addMinutes(15), function () {
            try {
                $resp = Http::timeout(4)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => self::LAT,
                    'longitude' => self::LON,
                    'current' => 'temperature_2m,weather_code',
                    'timezone' => 'America/Bogota',
                ]);

                if (! $resp->ok()) {
                    return null;
                }

                $actual = $resp->json('current');

                if (! $actual) {
                    return null;
                }

                return [
                    'temperatura' => round((float) ($actual['temperature_2m'] ?? 0)),
                    'descripcion' => $this->descripcion((int) ($actual['weather_code'] ?? 0)),
                ];
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    // Traduce el codigo WMO de Open-Meteo a una descripcion breve.
    private function descripcion(int $code): string
    {
        return match (true) {
            $code === 0 => 'Despejado',
            $code <= 3 => 'Parcialmente nublado',
            $code <= 48 => 'Niebla',
            $code <= 67 => 'Lluvia',
            $code <= 77 => 'Nieve',
            $code <= 82 => 'Chubascos',
            default => 'Tormenta',
        };
    }
}
