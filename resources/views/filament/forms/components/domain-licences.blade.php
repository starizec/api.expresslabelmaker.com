@php
    use App\Models\Domain;
    use App\Filament\Resources\LicencesResource;
    use Carbon\Carbon;
    
    // Access the record from the Livewire component
    $record = method_exists($this, 'getRecord') ? $this->getRecord() : null;
    $domain = $record ? Domain::with('licences')->find($record->id) : null;
    $licences = $domain ? $domain->licences : collect();
@endphp

<div class="space-y-4">
    @if($licences->count() > 0)
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Licencni ključ
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Korištenje
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Vrijedi od
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Vrijedi do
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Akcije
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($licences as $licence)
                        @php
                            $today = Carbon::now();
                            $validFromDate = $licence->valid_from instanceof Carbon 
                                ? $licence->valid_from 
                                : Carbon::parse($licence->valid_from);
                            $validUntilDate = $licence->valid_until 
                                ? ($licence->valid_until instanceof Carbon 
                                    ? $licence->valid_until 
                                    : Carbon::parse($licence->valid_until))
                                : null;

                            if ($validUntilDate && $today->between($validFromDate, $validUntilDate)) {
                                $status = 'Aktivna';
                                $statusClass = 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200';
                            } elseif ($validUntilDate && $today->gt($validUntilDate)) {
                                $status = 'Istekla';
                                $statusClass = 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200';
                            } else {
                                $status = 'Nadolazeća';
                                $statusClass = 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200';
                            }
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-gray-100">
                                {{ $licence->licence_uid }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                {{ $licence->usage }} / {{ $licence->usage_limit ?? '∞' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                {{ $validFromDate->format('d.m.Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                {{ $validUntilDate ? $validUntilDate->format('d.m.Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ LicencesResource::getUrl('edit', ['record' => $licence->id]) }}" 
                                   class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 underline">
                                    Uredi
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">Nema licenci za ovu domenu.</p>
    @endif
</div>
