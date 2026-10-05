<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AturanDiagnosa;

class AturanDiagnosaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['keluhan_text' => 'Mesin susah dihidupkan saat pagi hari atau kondisi dingin', 'diagnosa_awal' => 'Busi Aus / Aki Weak / Filter Bensin Kotor'],
            ['keluhan_text' => 'Ada bunyi mendecit keras dari kap mesin saat AC dinyalakan atau mobil digas', 'diagnosa_awal' => 'Fan Belt / V-Belt Kendur atau Aus'],
            ['keluhan_text' => 'Rem berbunyi mendecit atau berdecit tajam saat pedal rem diinjak', 'diagnosa_awal' => 'Kampas Rem (Brake Pad) Tipis / Piringan Cakram Aus'],
            ['keluhan_text' => 'Stir mobil bergetar hebat saat berada di kecepatan tinggi 80 100 km jam', 'diagnosa_awal' => 'Ban Perlu Spooring & Balancing / Velg Baling'],
            ['keluhan_text' => 'Mobil terasa melayang atau tidak stabil saat melewati jalan bergelombang', 'diagnosa_awal' => 'Shockbreaker Bocor / Bushing Arm Aus'],
            ['keluhan_text' => 'Ada bunyi kletuk kletuk dari area roda depan saat mobil belok patah', 'diagnosa_awal' => 'CV Joint / As Roda Luar Rusak'],
            ['keluhan_text' => 'AC mobil hanya keluar angin saja dan tidak dingin sama sekali', 'diagnosa_awal' => 'Freon Habis / Kebocoran Kondensor / Magnetic Clutch Rusak'],
            ['keluhan_text' => 'Lampu indikator temperatur mesin naik tinggi dan air radiator cepat berkurang', 'diagnosa_awal' => 'Radiator Bocor / Thermostat Macet / Water Pump Rusak'],
            ['keluhan_text' => 'Mesin mobil terasa pincang bergetar hebat dan tenaga berkurang drastis', 'diagnosa_awal' => 'Koil Pengapian (Ignition Coil) Mati / Busi Kotor'],
            ['keluhan_text' => 'Lampu Check Engine menyala terus menerus di meter cluster dashboard', 'diagnosa_awal' => 'Sensor Oksigen (O2) / Sensor MAF / Catalytic Converter Error'],
            ['keluhan_text' => 'Perpindahan gigi transmisi matic terasa tersendat jedug atau terlambat', 'diagnosa_awal' => 'Oli Transmisi Matic Kotor / Solenoid Transmisi Bermasalah'],
            ['keluhan_text' => 'Kaki kaki mobil berbunyi gluduk gluduk saat melewati jalan rusak atau polisi tidur', 'diagnosa_awal' => 'Link Stabilizer / Tie Rod / Ball Joint Aus'],
            ['keluhan_text' => 'Asap knalpot berwarna putih tebal dan oli mesin cepat berkurang', 'diagnosa_awal' => 'Ring Piston Aus / Seal Valve Bocor (Mesin Makan Oli)'],
            ['keluhan_text' => 'Pedal kopling terasa sangat keras diinjak atau ganti gigi manual terasa susah', 'diagnosa_awal' => 'Kampas Kopling Aus / Matahari Kopling (Pressure Plate) Rusak'],
            ['keluhan_text' => 'Keluar bau sangit terbakar dari area roda setelah mobil dipakai berjalan', 'diagnosa_awal' => 'Kaliper Rem Macet / Kampas Rem Terkunci'],
            ['keluhan_text' => 'Lampu indikator aki menyala saat mesin beroperasi', 'diagnosa_awal' => 'Alternator (Dinamo Ampere) Rusak / Tidak Mengisi'],
            ['keluhan_text' => 'Kaca mobil depan atau samping tidak bisa dinaik turunkan dari saklar', 'diagnosa_awal' => 'Power Window Motor / Saklar Power Window Rusak'],
            ['keluhan_text' => 'Mesin mendadak mati total saat sedang berjalan dan tidak bisa di start lagi', 'diagnosa_awal' => 'Tali Timing (Timing Belt) Putus / Sensor CKP Mati'],
            ['keluhan_text' => 'Setir mobil terasa sangat berat saat diputar saat parkir maupun jalan', 'diagnosa_awal' => 'Minyak Power Steering Habis / Pompa Power Steering Rusak'],
            ['keluhan_text' => 'Mobil menarik ke arah kiri atau kanan padahal setir posisi lurus', 'diagnosa_awal' => 'Setelan Kelurusan Roda Berubah (Butuh Spooring)'],
            ['keluhan_text' => 'Kaca mobil cepat berembun saat hujan dan AC menyala', 'diagnosa_awal' => 'Evaporator AC Kotor / Flapper Air Recirculation Rusak'],
            ['keluhan_text' => 'Ada tetesan oli hitam pekat di bawah kolong mobil saat parkir semalaman', 'diagnosa_awal' => 'Gasket Carter Oli / Seal Crankshaft Bocor'],
            ['keluhan_text' => 'Knalpot terdengar meletup letup atau suara knalpot terlalu bising', 'diagnosa_awal' => 'Bocor pada Packing Knalpot / Knalpot Keropos'],
            ['keluhan_text' => 'Saat pedal gas ditekan meraung tinggi tapi laju mobil sangat lambat', 'diagnosa_awal' => 'Kampas Kopling Selip (Manual/Matic)'],
            ['keluhan_text' => 'Klakson mobil tidak menyala dan tombol di setir tidak merespon', 'diagnosa_awal' => 'Kabel Spiral (Clockspring) Putus / Sekring Klakson Putus']
        ];

        foreach ($data as $item) {
            AturanDiagnosa::create($item);
        }
    }
}
