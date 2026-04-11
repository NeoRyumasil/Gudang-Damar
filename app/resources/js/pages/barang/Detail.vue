<script setup lang="ts">
defineProps<{
    barang: {
        id_barang: number;
        nama: string;
        harga: number;
        jumlah: number;
        total: number;
        ukuran: number;
        bentuk: string;
        ketebalan: string;
        bahan: string;
        guna_merek: string;
    };
}>();
</script>

<template>
    <div class="max-w-md mx-auto mt-10 bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-bold mb-4">Detail Barang</h2>

        <div class="info-line"><span>Nama</span><span>{{ barang.nama }}</span></div>
        <div class="info-line"><span>Ukuran</span><span>{{ barang.ukuran }}</span></div>
        <div class="info-line"><span>Bentuk</span><span>{{ barang.bentuk }}</span></div>
        <div class="info-line"><span>Ketebalan</span><span>{{ barang.ketebalan }}</span></div>
        <div class="info-line"><span>Bahan</span><span>{{ barang.bahan }}</span></div>
        <div class="info-line"><span>Merek</span><span>{{ barang.guna_merek }}</span></div>
        <div class="info-line"><span>Harga</span><span>Rp {{ barang.harga.toLocaleString('id-ID') }}</span></div>
        <div class="info-line"><span>Jumlah</span><span>{{ barang.jumlah }}</span></div>
        <div class="info-line"><span>Total</span><span>Rp {{ barang.total.toLocaleString('id-ID') }}</span></div>

        <div class="flex gap-3 mt-6">
            <a :href="`/barang/${barang.id_barang}/edit`"
               class="bg-yellow-500 text-white px-4 py-2 rounded-lg">
               Edit
            </a>
            <a href="/barang"
               class="bg-gray-400 text-white px-4 py-2 rounded-lg">
               Kembali
            </a>
        </div>
    </div>
</template>