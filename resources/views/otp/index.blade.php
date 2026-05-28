@php
    use App\Support\CountryFlag;
    use App\Support\ServiceIcon;

    $money = fn ($amount) => 'Rp'.number_format((float) $amount, 0, ',', '.');
    $countryId = (int) ($filters['country_id'] ?? 0);
    $serviceId = (int) ($filters['service_id'] ?? 0);
    $currentStep = $selectedCountry ? ($selectedService ? 3 : 2) : 1;
    $currentSort = $filters['sort'] ?? 'cheapest';
    $sortOptions = [
        'cheapest' => 'Termurah',
        'stock' => 'Stok terbesar',
        'newest' => 'Terbaru',
    ];

    $countryIso = fn ($country) => CountryFlag::isoCode($country->name, $country->iso_code);
    $countryFlagUrl = fn ($country) => $countryIso($country) ? 'https://flagcdn.com/w40/'.strtolower($countryIso($country)).'.png' : null;
    $countryInitial = fn ($country) => $countryIso($country) ?? strtoupper(substr($country->name, 0, 2));
    $serviceIconUrl = fn ($service) => ServiceIcon::url($service->name, $service->provider_code, $service->icon_url);
    $serviceInitial = fn ($service) => ServiceIcon::initials($service->name);

    $stepClass = fn ($step) => $currentStep >= $step
        ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
        : 'border-slate-200 bg-white text-slate-400';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Beli Nomor OTP</p>
                <h1 class="text-2xl font-bold text-slate-950">Pilih negara, layanan, lalu beli nomor</h1>
            </div>
            <a href="{{ route('otp.orders.index') }}" class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">Riwayat order</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center gap-2">
            @foreach ([1 => 'Negara', 2 => 'Layanan', 3 => 'Beli'] as $step => $label)
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm font-bold {{ $stepClass($step) }}">
                    <span class="grid h-6 w-6 place-items-center rounded-full {{ $currentStep >= $step ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400' }}">{{ $step }}</span>
                    {{ $label }}
                </div>
                @if ($step < 3)
                    <div class="hidden h-px w-10 bg-slate-200 sm:block"></div>
                @endif
            @endforeach
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <div class="text-sm font-semibold text-slate-500">Negara</div>
                <div class="mt-1 truncate text-lg font-bold text-slate-950">{{ $selectedCountry?->name ?? 'Belum dipilih' }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <div class="text-sm font-semibold text-slate-500">Layanan</div>
                <div class="mt-1 truncate text-lg font-bold text-slate-950">{{ $selectedService?->name ?? 'Belum dipilih' }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <div class="text-sm font-semibold text-slate-500">Saldo tersedia</div>
                <div class="mt-1 text-lg font-bold text-slate-950">{{ $money(auth()->user()->availableBalance()) }}</div>
            </div>
        </div>

        <section
            x-data="{
                countryQuery: @js($filters['country_q'] ?? ''),
                matchesCountry(name) {
                    return this.countryQuery.trim() === '' || name.toLowerCase().startsWith(this.countryQuery.trim().toLowerCase())
                },
            }"
            class="mt-6 rounded-lg border border-slate-200 bg-white"
        >
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800">1</span>
                        <h2 class="font-bold text-slate-950">Pilih Negara</h2>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">{{ $countries->count() }} negara dengan stok aktif</p>
                </div>

                <form method="GET" action="{{ route('otp.index') }}" class="w-full sm:max-w-sm">
                    @if ($serviceId)
                        <input type="hidden" name="service_id" value="{{ $serviceId }}">
                    @endif
                    <input
                        x-model.debounce.100ms="countryQuery"
                        name="country_q"
                        value="{{ $filters['country_q'] ?? '' }}"
                        placeholder="Cari negara..."
                        autocomplete="off"
                        class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    >
                </form>
            </div>

            <div class="grid max-h-[380px] gap-2 overflow-y-auto p-5 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                @forelse ($countries as $country)
                    @php
                        $flagUrl = $countryFlagUrl($country);
                    @endphp
                    <a
                        x-show="matchesCountry(@js($country->name))"
                        href="{{ route('otp.index', array_filter(['country_id' => $country->id])) }}"
                        class="flex items-center justify-between gap-3 rounded-lg border px-3 py-3 text-sm transition hover:border-emerald-300 hover:bg-emerald-50 {{ $countryId === $country->id ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 bg-white' }}"
                    >
                        <span class="flex min-w-0 items-center gap-3">
                            <span class="grid h-7 w-9 shrink-0 place-items-center overflow-hidden rounded bg-slate-100 text-xs font-black text-slate-700" title="{{ $countryInitial($country) }}">
                                @if ($flagUrl)
                                    <img src="{{ $flagUrl }}" alt="{{ $country->name }} flag" loading="lazy" class="h-full w-full object-cover">
                                @else
                                    {{ $countryInitial($country) }}
                                @endif
                            </span>
                            <span class="truncate font-bold text-slate-900">{{ $country->name }}</span>
                        </span>
                        <span class="text-xs font-semibold text-slate-400">{{ $country->active_prices_count }}</span>
                    </a>
                @empty
                    <div class="col-span-full px-5 py-10 text-center text-sm text-slate-500">Negara tidak ditemukan.</div>
                @endforelse
            </div>
        </section>

        <section
            x-data="{
                serviceQuery: @js($filters['service_q'] ?? ''),
                matchesService(name) {
                    return this.serviceQuery.trim() === '' || name.toLowerCase().startsWith(this.serviceQuery.trim().toLowerCase())
                },
            }"
            class="mt-6 rounded-lg border border-slate-200 bg-white {{ $selectedCountry ? '' : 'opacity-60' }}"
        >
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800">2</span>
                        <h2 class="font-bold text-slate-950">Pilih Layanan</h2>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">{{ $selectedCountry ? $services->count().' layanan tersedia untuk '.$selectedCountry->name : 'Pilih negara terlebih dahulu' }}</p>
                </div>

                @if ($selectedCountry)
                    <form method="GET" action="{{ route('otp.index') }}" class="w-full sm:max-w-sm">
                        <input type="hidden" name="country_id" value="{{ $selectedCountry->id }}">
                        <input
                            x-model.debounce.100ms="serviceQuery"
                            name="service_q"
                            value="{{ $filters['service_q'] ?? '' }}"
                            placeholder="Cari layanan..."
                            autocomplete="off"
                            class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                    </form>
                @endif
            </div>

            <div class="grid max-h-[360px] gap-2 overflow-y-auto p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @if (! $selectedCountry)
                    <div class="col-span-full rounded-lg bg-slate-50 px-5 py-10 text-center text-sm font-semibold text-slate-500">Negara belum dipilih.</div>
                @else
                    @forelse ($services as $service)
                        @php
                            $iconUrl = $serviceIconUrl($service);
                        @endphp
                        <a
                            x-show="matchesService(@js($service->name))"
                            href="{{ route('otp.index', ['country_id' => $selectedCountry->id, 'service_id' => $service->id]) }}"
                            class="flex items-center gap-3 rounded-lg border px-4 py-3 transition hover:border-emerald-300 hover:bg-emerald-50 {{ $serviceId === $service->id ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 bg-white' }}"
                        >
                            <span class="grid h-9 w-9 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-100 p-2 text-xs font-black text-slate-700">
                                @if ($iconUrl)
                                    <img src="{{ $iconUrl }}" alt="{{ $service->name }} icon" loading="lazy" class="h-full w-full object-contain" onerror="if(!this.getAttribute('data-tried-favicon')){this.setAttribute('data-tried-favicon','true');this.src='https://www.google.com/s2/favicons?sz=64&domain='+this.src.split('/').pop();}else{this.style.display='none';this.parentElement.textContent='{{ $serviceInitial($service) }}';}">
                                @else
                                    {{ $serviceInitial($service) }}
                                @endif
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate font-bold text-slate-950">{{ $service->name }}</span>
                                <span class="mt-1 block truncate text-sm text-slate-500">Code {{ $service->provider_code }} &middot; {{ $service->active_prices_count }} pilihan</span>
                            </span>
                        </a>
                    @empty
                        <div class="col-span-full px-5 py-10 text-center text-sm text-slate-500">Layanan tidak ditemukan untuk negara ini.</div>
                    @endforelse
                @endif
            </div>
        </section>

        <section class="mt-6 rounded-lg border border-slate-200 bg-white {{ $selectedService ? '' : 'opacity-60' }}">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800">3</span>
                        <h2 class="font-bold text-slate-950">Pilih Harga & Beli</h2>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">{{ $selectedCountry && $selectedService ? $selectedService->name.' di '.$selectedCountry->name : 'Pilih layanan terlebih dahulu' }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if ($selectedCountry && $selectedService)
                        <form method="GET" action="{{ route('otp.index') }}" class="flex items-center gap-2">
                            <input type="hidden" name="country_id" value="{{ $selectedCountry->id }}">
                            <input type="hidden" name="service_id" value="{{ $selectedService->id }}">
                            @if (! empty($filters['country_q']))
                                <input type="hidden" name="country_q" value="{{ $filters['country_q'] }}">
                            @endif
                            @if (! empty($filters['service_q']))
                                <input type="hidden" name="service_q" value="{{ $filters['service_q'] }}">
                            @endif
                            <label for="price-sort" class="sr-only">Urutkan harga</label>
                            <select
                                id="price-sort"
                                name="sort"
                                onchange="this.form.submit()"
                                class="rounded-lg border-slate-300 text-sm font-bold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                @foreach ($sortOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($currentSort === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif

                    @if ($selectedCountry)
                        <form method="POST" action="{{ route('otp.refresh-current') }}">
                            @csrf
                            <input type="hidden" name="country_id" value="{{ $selectedCountry->id }}">
                            @if ($selectedService)
                                <input type="hidden" name="service_id" value="{{ $selectedService->id }}">
                            @endif
                            <button class="inline-flex rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-800 hover:bg-emerald-100">
                                Refresh scope ini
                            </button>
                        </form>
                    @endif

                    @if ($selectedCountry || $selectedService)
                        <a href="{{ route('otp.index') }}" class="inline-flex rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Reset pilihan</a>
                    @endif
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @if (! $selectedService)
                    <div class="px-5 py-10 text-center text-sm font-semibold text-slate-500">Layanan belum dipilih.</div>
                @else
                    @forelse ($prices as $price)
                        @php
                            $providerVariant = $price->provider_meta['provider_id'] ?? null;
                            $variantLabel = $providerVariant ? 'Provider '.$providerVariant : str_replace(['provider:', 'price:', 'default'], ['Provider ', 'Harga ', 'Default'], $price->provider_price_key);
                            $priceServiceIconUrl = $serviceIconUrl($price->otpService);
                        @endphp
                        <div class="grid gap-4 px-5 py-4 lg:grid-cols-[1fr_0.8fr_auto] lg:items-center">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 grid h-10 w-10 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-100 p-2 text-xs font-black text-slate-700">
                                    @if ($priceServiceIconUrl)
                                        <img src="{{ $priceServiceIconUrl }}" alt="{{ $price->otpService->name }} icon" loading="lazy" class="h-full w-full object-contain" onerror="if(!this.getAttribute('data-tried-favicon')){this.setAttribute('data-tried-favicon','true');this.src='https://www.google.com/s2/favicons?sz=64&domain='+this.src.split('/').pop();}else{this.style.display='none';this.parentElement.textContent='{{ $serviceInitial($price->otpService) }}';}">
                                    @else
                                        {{ $serviceInitial($price->otpService) }}
                                    @endif
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-base font-bold text-slate-950">{{ $price->otpService->name }}</span>
                                    <span class="mt-1 flex flex-wrap gap-2 text-sm text-slate-500">
                                        <span>{{ $price->country->name }}</span>
                                        <span>Code {{ $price->otpService->provider_code }}/{{ $price->country->provider_code }}</span>
                                        <span>{{ $variantLabel }}</span>
                                    </span>
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                    <div class="text-xs font-semibold text-slate-500">Harga</div>
                                    <div class="mt-1 font-bold text-slate-950">{{ $money($price->selling_price) }}</div>
                                </div>
                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                    <div class="text-xs font-semibold text-slate-500">Stok</div>
                                    <div class="mt-1 font-bold text-slate-950">{{ number_format((int) $price->stock_count, 0, ',', '.') }}</div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('otp.orders.store') }}" class="flex lg:justify-end">
                                @csrf
                                <input type="hidden" name="service_price_id" value="{{ $price->id }}">
                                <button @disabled((int) $price->stock_count < 1) class="w-full rounded-lg bg-slate-950 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300 lg:w-auto">
                                    {{ (int) $price->stock_count < 1 ? 'Stok habis' : 'Beli' }}
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-slate-500">Harga belum tersedia untuk kombinasi ini.</div>
                    @endforelse
                @endif
            </div>
        </section>
    </div>
</x-app-layout>
