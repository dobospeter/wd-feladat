<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Modules\Party\Models\Party;
use App\Modules\Device\Models\Device;
use App\Modules\Contract\Models\Contract;
use App\Modules\Contract\Models\ContractDevice;

/**
 * Fixed demo data (parties, device types, contracts and the units placed on them), so the API can be listed right
 * after installing. Every record is looked up by its natural key first, so running the seeder again changes nothing.
 */
final class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $parties = $this->parties();
            $devices = $this->devices();
            $contracts = $this->contracts($parties);

            $this->placeDevices($contracts, $devices);
        });
    }

    /**
     * @return array<string, Party>
     */
    private function parties(): array
    {
        $rows = [
            'happy' => ['name' => 'Happy Gastro Kft.', 'tax_number' => '26123456-2-41', 'address' => '1051 Budapest, Széchenyi István tér 7.', 'email' => 'support@happygastro.hu', 'phone' => '+36 1 234 5678'],
            'bistro' => ['name' => 'Bistro Central Zrt.', 'tax_number' => '14987654-2-13', 'address' => '4025 Debrecen, Piac utca 12.', 'email' => 'iroda@bistrocentral.hu', 'phone' => '+36 52 123 456'],
            'hotel' => ['name' => 'Hotel Panoráma Kft.', 'tax_number' => '23555111-2-08', 'address' => '8230 Balatonfüred, Kisfaludy utca 3.', 'email' => 'gazdasag@hotelpanorama.hu', 'phone' => '+36 87 555 010'],
            'kati' => ['name' => 'Kovács Katalin e.v.', 'tax_number' => null, 'address' => '6720 Szeged, Dugonics tér 2.', 'email' => 'kati@example.com', 'phone' => null],
        ];

        return array_map(
            static fn (array $row): Party => Party::query()->firstOrCreate(['name' => $row['name']], $row),
            $rows,
        );
    }

    /**
     * @return array<string, Device>
     */
    private function devices(): array
    {
        $rows = [
            'oven' => ['name' => 'Kombipároló iCombi Pro 10-1/1', 'manufacturer' => 'Rational', 'model' => 'iCombi Pro 10-1/1', 'description' => 'Elektromos kombipároló, 10 GN 1/1'],
            'pos' => ['name' => 'POS terminál', 'manufacturer' => 'Verifone', 'model' => 'V240m', 'description' => 'Mobil bankkártya-terminál'],
            'printer' => ['name' => 'Blokknyomtató', 'manufacturer' => 'Epson', 'model' => 'TM-T20III', 'description' => 'Hőpapíros blokknyomtató, USB/Ethernet'],
            'laptop' => ['name' => 'Üzleti laptop', 'manufacturer' => 'Dell', 'model' => 'Latitude 5440', 'description' => '14 colos üzleti laptop'],
            'cooler' => ['name' => 'Hűtött pult', 'manufacturer' => 'Bartscher', 'model' => 'Cool 3200', 'description' => null],
            'scale' => ['name' => 'Mérleg', 'manufacturer' => 'Mettler Toledo', 'model' => 'Viva 15', 'description' => 'Kereskedelmi mérleg, 15 kg'],
        ];

        return array_map(
            static fn (array $row): Device => Device::query()->firstOrCreate(
                ['manufacturer' => $row['manufacturer'], 'model' => $row['model']],
                $row,
            ),
            $rows,
        );
    }

    /**
     * @param  array<string, Party>  $parties
     * @return array<string, Contract>
     */
    private function contracts(array $parties): array
    {
        $rows = [
            'c1' => ['contract_number' => 'SZ-2026-001', 'party' => 'happy', 'starts_on' => '2026-01-01', 'ends_on' => '2026-12-31', 'notes' => 'Éves bérleti és szervizszerződés'],
            'c2' => ['contract_number' => 'SZ-2026-002', 'party' => 'bistro', 'starts_on' => '2026-03-01', 'ends_on' => '2028-02-29', 'notes' => 'Kétéves eszközbérlet'],
            'c3' => ['contract_number' => 'SZ-2026-003', 'party' => 'hotel', 'starts_on' => '2026-06-15', 'ends_on' => null, 'notes' => 'Határozatlan idejű keretszerződés'],
            'c4' => ['contract_number' => 'SZ-2025-017', 'party' => 'happy', 'starts_on' => '2025-01-01', 'ends_on' => '2025-12-31', 'notes' => 'Lejárt szerződés (archív)'],
            'c5' => ['contract_number' => 'SZ-2026-004', 'party' => 'kati', 'starts_on' => '2026-09-01', 'ends_on' => '2027-08-31', 'notes' => null],
        ];

        return array_map(static function (array $row) use ($parties): Contract {
            $party = $row['party'];
            unset($row['party']);

            return Contract::query()->firstOrCreate(
                ['contract_number' => $row['contract_number']],
                $row + ['party_id' => $parties[$party]->id],
            );
        }, $rows);
    }

    /**
     * The same device type is on several contracts, always with a different serial number.
     *
     * @param  array<string, Contract>  $contracts
     * @param  array<string, Device>  $devices
     */
    private function placeDevices(array $contracts, array $devices): void
    {
        $placements = [
            ['c1', 'oven', 'RAT-IC10-000101'], ['c1', 'oven', 'RAT-IC10-000102'], ['c1', 'pos', 'VF-240-5501'], ['c1', 'printer', 'EPS-T20-A1001'],
            ['c2', 'oven', 'RAT-IC10-000201'], ['c2', 'cooler', 'BAR-C32-7001'], ['c2', 'pos', 'VF-240-5502'], ['c2', 'scale', 'MT-V15-3301'],
            ['c3', 'laptop', 'DELL-5440-9001'], ['c3', 'laptop', 'DELL-5440-9002'], ['c3', 'printer', 'EPS-T20-A1002'], ['c3', 'pos', 'VF-240-5503'],
            ['c4', 'cooler', 'BAR-C32-6001'], ['c4', 'scale', 'MT-V15-2201'],
            ['c5', 'scale', 'MT-V15-3302'], ['c5', 'printer', 'EPS-T20-A1003'],
        ];

        foreach ($placements as [$contract, $device, $serialNumber]) {
            ContractDevice::query()->firstOrCreate(
                ['device_id' => $devices[$device]->id, 'serial_number' => $serialNumber],
                ['contract_id' => $contracts[$contract]->id],
            );
        }
    }
}
