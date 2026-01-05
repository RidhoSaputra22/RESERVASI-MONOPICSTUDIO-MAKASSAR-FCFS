<?php

namespace Database\Seeders;

use App\Models\Studio;
use App\Models\Package;
use App\Models\Category;
use App\Models\Photographer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
Studio::factory()->count(1)->create([
    'name' => 'Monopic Studio',
    'location' => 'Jl. Merpati No. 123, Jakarta',
]);

Photographer::factory()->count(5)->create([
    'is_available' => true,
]);

Category::factory()->create([
    'name' => 'Foto Grup',
    'keterangan' => '{
        "type":"doc",
        "content":[
            {
                "type":"bulletList",
                "content":[
                    {
                        "type":"listItem",
                        "content":[{"type":"paragraph","content":[{"type":"text","text":"Tidak berlaku untuk prewedding, maternity, ulang tahun, dan wisuda"}]}]
                    },
                    {
                        "type":"listItem",
                        "content":[{"type":"paragraph","content":[{"type":"text","text":"Penambahan background dikenakan 2× harga paket"}]}]
                    },
                    {
                        "type":"listItem",
                        "content":[{"type":"paragraph","content":[{"type":"text","text":"Ganti kostum atau baju dikenakan 2× harga paket"}]}]
                    },
                    {
                        "type":"listItem",
                        "content":[{"type":"paragraph","content":[{"type":"text","text":"Tidak bisa melakukan foto pisah-pisah"}]}]
                    },
                    {
                        "type":"listItem",
                        "content":[{"type":"paragraph","content":[{"type":"text","text":"Studio tidak menyediakan make up dan kostum"}]}]
                    },
                    {
                        "type":"listItem",
                        "content":[{"type":"paragraph","content":[{"type":"text","text":"Lebih dari 20 orang dikenakan tambahan Rp 10.000 per orang"}]}]
                    },
                    {
                        "type":"listItem",
                        "content":[{"type":"paragraph","content":[{"type":"text","text":"Lepas almamater diberikan secara gratis"}]}]
                    },
                    {
                        "type":"listItem",
                        "content":[{"type":"paragraph","content":[{"type":"text","text":"Durasi pemotretan maksimal 10 menit"}]}]
                    },
                    {
                        "type":"listItem",
                        "content":[{"type":"paragraph","content":[{"type":"text","text":"Overtime dikenakan Rp 50.000 per 5 menit dan dihitung dari menit pertama"}]}]
                    },
                    {
                        "type":"listItem",
                        "content":[{"type":"paragraph","content":[{"type":"text","text":"Penambahan kutipan foto dikenakan Rp 50.000 per 5 kutipan"}]}]
                    }
                ]
            }
        ]
    }'
]);

Category::factory()->create([
    'name' => 'Foto Family',
    'keterangan' => '{
        "type":"doc",
        "content":[
            {
                "type":"bulletList",
                "content":[
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Tidak berlaku untuk prewedding dan ulang tahun"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Penambahan background dikenakan 2× harga paket"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Ganti kostum atau baju dikenakan 2× harga paket"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Sudah bisa melakukan foto pisah-pisah"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Studio tidak menyediakan make up dan kostum"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Penambahan orang dikenakan biaya Rp 10.000 per orang"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Lepas almamater atau jubah diberikan secara gratis"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Overtime dikenakan Rp 50.000 per 5 menit"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Penambahan kutipan foto dikenakan Rp 50.000 per 5 kutipan"}]}]}
                ]
            }
        ]
    }'
]);


Category::factory()->create([
    'name' => 'Foto Single / Personal',
    'keterangan' => '{
        "type":"doc",
        "content":[
            {
                "type":"bulletList",
                "content":[
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Tidak berlaku untuk prewedding, maternity, dan wisuda"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Penambahan background dikenakan 2× harga normal"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Ganti baju atau kostum dikenakan 2× harga normal"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"File hasil foto di Google Drive tersedia maksimal 30 hari setelah pemotretan"}]}]}
                ]
            }
        ]
    }'
]);
Category::factory()->create([
    'name' => 'Pas Foto',
    'keterangan' => null,
]);

Package::factory()->createMany([
    [
        'name' => 'Foto Grup A',
        'slug' => 'foto-grup-a',
        'description' => 'Paket foto grup untuk 3 sampai 5 orang.',
        'fasilitas' => '{
            "type":"doc",
            "content":[{"type":"bulletList","content":[
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 sesi / background"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 baju pribadi"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"20 kutipan foto"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 cetak ukuran 10R"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"All file Google Drive"}]}]}
            ]}]
        }',
        'price' => 100000,
        'duration_minutes' => 10,
        'category_id' => 1,
    ],
    [
        'name' => 'Foto Grup B',
        'slug' => 'foto-grup-b',
        'description' => 'Paket foto grup untuk 6 sampai 10 orang.',
        'fasilitas' => '{
            "type":"doc",
            "content":[{"type":"bulletList","content":[
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 sesi / background"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 baju pribadi"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"20 kutipan foto"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 cetak ukuran 10R"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"All file Google Drive"}]}]}
            ]}]
        }',
        'price' => 150000,
        'duration_minutes' => 10,
        'category_id' => 1,
    ],
    [
        'name' => 'Foto Grup C',
        'slug' => 'foto-grup-c',
        'description' => 'Paket foto grup untuk 11 sampai 15 orang.',
        'fasilitas' => '{
            "type":"doc",
            "content":[{"type":"bulletList","content":[
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 sesi / background"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 baju pribadi"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"20 kutipan foto"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 cetak ukuran 10R"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"All file Google Drive"}]}]}
            ]}]
        }',
        'price' => 200000,
        'duration_minutes' => 10,
        'category_id' => 1,
    ],
    [
        'name' => 'Foto Grup D',
        'slug' => 'foto-grup-d',
        'description' => 'Paket foto grup untuk 16 sampai 20 orang.',
        'fasilitas' => '{
            "type":"doc",
            "content":[{"type":"bulletList","content":[
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 sesi / background"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 baju pribadi"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"20 kutipan foto"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 cetak ukuran 10R"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"All file Google Drive"}]}]}
            ]}]
        }',
        'price' => 250000,
        'duration_minutes' => 10,
        'category_id' => 1,
    ],
]);


Package::factory()->createMany([
    [
        'name' => 'Foto Family Basic',
        'slug' => 'foto-family-basic',
        'description' => 'Paket foto keluarga basic untuk 1 sampai 5 orang.',
        'fasilitas' => '{
            "type":"doc",
            "content":[{"type":"bulletList","content":[
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"30 kutipan foto"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Durasi pemotretan 10 menit"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 sesi / background"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"2 cetak ukuran 10R"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"All file Google Drive"}]}]}
            ]}]
        }',
        'price' => 200000,
        'duration_minutes' => 10,
        'category_id' => 2, // Foto Family
    ],
    [
        'name' => 'Foto Family Gold',
        'slug' => 'foto-family-gold',
        'description' => 'Paket foto keluarga gold untuk hingga 10 orang.',
        'fasilitas' => '{
            "type":"doc",
            "content":[{"type":"bulletList","content":[
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Foto tanpa batasan jumlah"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Durasi pemotretan 20 menit"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 sesi / background"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"2 cetak ukuran 10R"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"All file Google Drive"}]}]}
            ]}]
        }',
        'price' => 300000,
        'duration_minutes' => 20,
        'category_id' => 2,
    ],
    [
        'name' => 'Foto Family Platinum',
        'slug' => 'foto-family-platinum',
        'description' => 'Paket foto keluarga premium dengan frame.',
        'fasilitas' => '{
            "type":"doc",
            "content":[{"type":"bulletList","content":[
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Foto tanpa batasan jumlah"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Durasi pemotretan 20 menit"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 sesi / background"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 cetak ukuran 16R beserta frame"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"2 cetak ukuran 10R"}]}]},
                {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"All file Google Drive"}]}]}
            ]}]
        }',
        'price' => 550000,
        'duration_minutes' => 20,
        'category_id' => 2,
    ],
]);

Package::factory()->create([
    'name' => 'Foto Single / Personal',
    'slug' => 'foto-single-personal',
    'description' => 'Paket foto personal untuk satu orang.',
    'fasilitas' => '{
        "type":"doc",
        "content":[
            {
                "type":"bulletList",
                "content":[
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 kostum"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 sesi / background"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"20 kutipan foto"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"1 cetak ukuran 10R"}]}]},
                    {"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"All file Google Drive"}]}]}
                ]
            }
        ]
    }',
    'price' => 100000,
    'duration_minutes' => 10,
    'category_id' => 3, // Sesuaikan dengan ID kategori Foto Single / Personal
]);

Package::factory()->create([
    'name' => 'Pas Foto',
    'slug' => 'pas-foto',
    'description' => 'Paket pas foto untuk kebutuhan administrasi dan dokumen resmi.',
    'fasilitas' => '{
        "type":"doc",
        "content":[
            {
                "type":"bulletList",
                "content":[
                    {
                        "type":"listItem",
                        "content":[
                            {"type":"paragraph","content":[{"type":"text","text":"1 kostum"}]}
                        ]
                    },
                    {
                        "type":"listItem",
                        "content":[
                            {"type":"paragraph","content":[{"type":"text","text":"1 sesi / background"}]}
                        ]
                    },
                    {
                        "type":"listItem",
                        "content":[
                            {"type":"paragraph","content":[{"type":"text","text":"1–5 kali kutipan foto"}]}
                        ]
                    },
                    {
                        "type":"listItem",
                        "content":[
                            {"type":"paragraph","content":[{"type":"text","text":"1 foto diedit"}]}
                        ]
                    },
                    {
                        "type":"listItem",
                        "content":[
                            {"type":"paragraph","content":[{"type":"text","text":"5 cetak ukuran 4×6"}]}
                        ]
                    },
                    {
                        "type":"listItem",
                        "content":[
                            {"type":"paragraph","content":[{"type":"text","text":"5 cetak ukuran 3×4"}]}
                        ]
                    },
                    {
                        "type":"listItem",
                        "content":[
                            {"type":"paragraph","content":[{"type":"text","text":"5 cetak ukuran 2×3"}]}
                        ]
                    },
                    {
                        "type":"listItem",
                        "content":[
                            {"type":"paragraph","content":[{"type":"text","text":"All file Google Drive"}]}
                        ]
                    }
                ]
            }
        ]
    }',
    'price' => 50000,
    'duration_minutes' => 10,
    'category_id' => 4, // Sesuaikan dengan ID kategori Pas Foto
]);


}}
