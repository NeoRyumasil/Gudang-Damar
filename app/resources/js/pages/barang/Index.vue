<script setup lang="ts">
import { router } from '@inertiajs/vue3';

defineProps<{
    barangList: Array<{
        id_barang: number;
        nama: string;
        harga: number;
        jumlah: number;
        total: number;
    }>;
}>();

function hapus(id: number) {
    if (confirm('Yakin ingin menghapus barang ini?')) {
        router.delete(`/barang/${id}`);
    }
}
</script>

<template>
    <div class="max-w-5xl mx-auto mt-10">
        <h1 class="text-2xl font-bold mb-5">List Barang</h1>

        <div class="btn-wrapper mb-4">
            <a href="/barang/create" class="btn-tambah">+ Tambah Barang</a>
        </div>

        <div v-if="$page.props.flash?.success"
             class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ $page.props.flash.success }}
        </div>

        <table class="w-full bg-white rounded-xl shadow">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-3">Nama</th>
                    <th class="p-3">Harga</th>
                    <th class="p-3">Jumlah</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="barang in barangList" :key="barang.id_barang"
                    class="border-t text-center">
                    <td class="p-3">{{ barang.nama }}</td>
                    <td class="p-3">
                        Rp {{ barang.harga.toLocaleString('id-ID') }}
                    </td>
                    <td class="p-3">{{ barang.jumlah }}</td>
                    <td class="p-3 space-x-2">
                        <a :href="`/barang/${barang.id_barang}`"
                           class="bg-blue-500 text-white px-3 py-1 rounded">
                           Detail
                        </a>
                        <a :href="`/barang/${barang.id_barang}/edit`"
                           class="bg-yellow-500 text-white px-3 py-1 rounded">
                           Edit
                        </a>
                        <button @click="hapus(barang.id_barang)"
                                class="bg-red-500 text-white px-3 py-1 rounded">
                            Hapus
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>