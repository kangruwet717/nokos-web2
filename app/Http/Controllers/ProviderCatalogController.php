<?php

namespace App\Http\Controllers;

use App\Jobs\SyncSmsbowerCatalogJob;
use App\Models\Country;
use App\Models\OtpService;
use App\Models\ServicePrice;
use App\Services\Providers\ProviderSyncTracker;
use App\Support\ProviderSyncStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProviderCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'country_q' => ['nullable', 'string', 'max:80'],
            'service_q' => ['nullable', 'string', 'max:80'],
            'service_id' => ['nullable', 'integer', 'exists:otp_services,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'sort' => ['nullable', 'in:cheapest,stock,newest'],
        ]);

        $selectedCountry = $request->filled('country_id')
            ? Country::query()
                ->where('is_active', true)
                ->where('is_blacklisted', false)
                ->find($validated['country_id'])
            : null;

        $selectedService = $request->filled('service_id')
            ? OtpService::query()
                ->where('is_active', true)
                ->where('is_blacklisted', false)
                ->find($validated['service_id'])
            : null;

        $services = OtpService::query()
            ->where('is_active', true)
            ->where('is_blacklisted', false)
            ->whereHas('prices', function ($query) use ($selectedCountry) {
                $query
                    ->where('is_active', true)
                    ->where('stock_count', '>', 0)
                    ->when($selectedCountry, fn ($query) => $query->where('country_id', $selectedCountry->id));
            })
            ->withCount(['prices as active_prices_count' => function ($query) use ($selectedCountry) {
                $query
                    ->where('is_active', true)
                    ->where('stock_count', '>', 0)
                    ->when($selectedCountry, fn ($query) => $query->where('country_id', $selectedCountry->id));
            }])
            ->when($request->filled('service_q'), fn ($query) => $query->where('name', 'like', $validated['service_q'].'%'))
            ->orderByRaw("case 
                when name = 'WhatsApp' then 0
                when name = 'Telegram' then 1
                when name = 'Google' then 2
                when name = 'Google Chat' then 3
                when name = 'Facebook' then 4
                when name = 'Instagram' then 5
                when name = 'TikTok' then 6
                when name = 'YouTube' then 7
                when name in ('Twitter', 'X') then 8
                when name = 'Discord' then 9
                when name = 'Netflix' then 10
                when name = 'Spotify' then 11
                when name = 'Snapchat' then 12
                when name = 'LinkedIn' then 13
                when name = 'Reddit' then 14
                when name = 'PayPal' then 15
                when name = 'Amazon' then 16
                when name = 'Apple' then 17
                when name = 'Microsoft' then 18
                when name = 'Steam' then 19
                when name = 'Binance' then 20
                when name = 'Shopee' then 21
                when name = 'Tokopedia' then 22
                when name = 'Gojek' then 23
                when name = 'Grab' then 24
                when name = 'LINE' then 25
                when name = 'KakaoTalk' then 26
                when name = 'WeChat' then 27
                when name = 'Viber' then 28
                when name = 'Signal' then 29
                when name = 'Skype' then 30
                when name = 'Tinder' then 31
                when name = 'Uber' then 32
                when name = 'Airbnb' then 33
                when name = 'Yahoo' then 34
                when name = 'Naver' then 35
                when name = 'Bolt' then 36
                when name = 'Aws' then 37
                when name = 'Adobe' then 38
                when name = 'Textnow' then 39
                when name = 'ShareChat' then 40
                when name = 'Tumblr' then 41
                when name = 'DocuSign' then 42
                when name = 'DigitalOcean' then 43
                when name = 'Cursor' then 44
                when name = 'Grok' then 45
                else 99 
            end")
            ->orderBy('name');

        if (! $selectedCountry && ! $request->filled('service_q')) {
            $services->limit(120);
        }

        $services = $services->get();

        $countries = Country::query()
            ->where('is_active', true)
            ->where('is_blacklisted', false)
            ->whereHas('prices', fn ($query) => $query->where('is_active', true)->where('stock_count', '>', 0))
            ->withCount(['prices as active_prices_count' => fn ($query) => $query->where('is_active', true)->where('stock_count', '>', 0)])
            ->when($request->filled('country_q'), fn ($query) => $query->where('name', 'like', $validated['country_q'].'%'))
            ->orderByRaw('case when name = ? then 0 else 1 end', ['Indonesia'])
            ->orderBy('name')
            ->limit(240)
            ->get();

        $pricesQuery = ServicePrice::query()
            ->with(['otpService', 'country'])
            ->where('is_active', true)
            ->whereHas('otpService', fn ($query) => $query->where('is_active', true)->where('is_blacklisted', false))
            ->whereHas('country', fn ($query) => $query->where('is_active', true)->where('is_blacklisted', false))
            ->when($request->filled('service_id'), fn ($query) => $query->where('otp_service_id', $validated['service_id']))
            ->when($request->filled('country_id'), fn ($query) => $query->where('country_id', $validated['country_id']))
            ->when($request->filled('q'), function ($query) use ($validated) {
                $search = $validated['q'];

                $query->where(function ($query) use ($search) {
                    $query->whereHas('otpService', fn ($serviceQuery) => $serviceQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('country', fn ($countryQuery) => $countryQuery->where('name', 'like', "%{$search}%"));
                });
            });

        match ($validated['sort'] ?? 'cheapest') {
            'stock' => $pricesQuery->orderByDesc('stock_count')->orderBy('selling_price'),
            'newest' => $pricesQuery->latest('last_synced_at'),
            default => $pricesQuery->orderBy('selling_price'),
        };

        $prices = $pricesQuery
            ->limit(100)
            ->get();

        return view('otp.index', [
            'services' => $services,
            'countries' => $countries,
            'prices' => $prices,
            'filters' => $validated,
            'selectedCountry' => $selectedCountry,
            'selectedService' => $selectedService,
        ]);
    }

    public function services(Request $request): JsonResponse
    {
        $services = OtpService::query()
            ->where('is_active', true)
            ->where('is_blacklisted', false)
            ->when($request->query('q'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'provider_code', 'name']);

        return response()->json($services);
    }

    public function prices(Request $request): JsonResponse
    {
        $prices = ServicePrice::query()
            ->with(['otpService:id,name,provider_code', 'country:id,name,provider_code'])
            ->where('is_active', true)
            ->when($request->integer('service_id'), fn ($query, $serviceId) => $query->where('otp_service_id', $serviceId))
            ->when($request->integer('country_id'), fn ($query, $countryId) => $query->where('country_id', $countryId))
            ->orderBy('selling_price')
            ->limit(50)
            ->get();

        return response()->json($prices);
    }

    public function refreshCurrent(Request $request, ProviderSyncTracker $tracker): RedirectResponse
    {
        $validated = $request->validate([
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'service_id' => ['nullable', 'integer', 'exists:otp_services,id'],
        ]);

        $country = Country::query()
            ->where('is_active', true)
            ->where('is_blacklisted', false)
            ->findOrFail($validated['country_id']);
        $service = isset($validated['service_id'])
            ? OtpService::query()
                ->where('is_active', true)
                ->where('is_blacklisted', false)
                ->findOrFail($validated['service_id'])
            : null;

        $scope = $service
            ? "Scope {$service->name} di {$country->name}."
            : "Scope semua layanan di {$country->name}.";

        $cooldown = (int) config('services.smsbower.scope_sync_cooldown_seconds', 120);
        $recentScope = $tracker->recentlyQueuedOrSynced($service?->provider_code, (string) $country->provider_code, $cooldown);

        if ($recentScope) {
            return back()->with('status', 'Refresh tidak dibuat ulang. '.$scope.' Status terakhir: '.$recentScope->status.'.');
        }

        $log = $tracker->markQueued($service?->provider_code, (string) $country->provider_code, $request->user()?->id);
        ProviderSyncStatus::markQueued($scope);
        SyncSmsbowerCatalogJob::dispatch($service?->provider_code, (string) $country->provider_code, $log->id);

        return back()->with('status', 'Refresh katalog diproses untuk '.$scope);
    }
}
