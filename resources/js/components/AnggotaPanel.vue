<template>
  <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-lg font-semibold text-white">Daftar Anggota</h2>
        <p class="mt-1 text-sm text-slate-400">Input data anggota untuk kebutuhan kegiatan.</p>
      </div>
      <button
        class="w-full rounded-lg border border-slate-700 px-3 py-1 text-sm text-slate-300 hover:border-slate-500 sm:w-auto"
        @click="load"
      >
        Refresh
      </button>
    </div>

    <div v-if="error" class="mt-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </div>

    <form class="mt-5 grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
      <label class="grid gap-2 text-sm text-slate-300 sm:col-span-2">
        Nama
        <input v-model="form.nama" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" required />
      </label>
      <label class="grid gap-2 text-sm text-slate-300">
        Nomor HP
        <input v-model="form.telepon" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" />
      </label>
      <label class="grid gap-2 text-sm text-slate-300">
        Email
        <input v-model="form.email" type="email" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" />
      </label>

      <div class="flex flex-wrap gap-3 sm:col-span-2">
        <button
          class="w-full rounded-lg bg-emerald-500 px-5 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 sm:w-auto"
        >
          {{ isEditing ? 'Simpan Perubahan' : 'Tambah Anggota' }}
        </button>
        <button
          v-if="isEditing"
          type="button"
          class="w-full rounded-lg border border-slate-700 px-5 py-2 text-sm text-slate-200 hover:border-slate-500 sm:w-auto"
          @click="cancelEdit"
        >
          Batal
        </button>
      </div>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-800">
      <table class="min-w-[680px] w-full border-collapse text-[11px] sm:text-xs">
        <thead class="bg-slate-900/80 text-slate-200">
          <tr>
            <th class="border border-slate-800 px-2 py-2 text-center">No</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Nama</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Nomor HP</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Email</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td class="border border-slate-800 px-3 py-2 text-center text-slate-400" colspan="5">Memuat data...</td>
          </tr>
          <tr v-else-if="items.length === 0">
            <td class="border border-slate-800 px-3 py-2 text-center text-slate-400" colspan="5">Belum ada anggota.</td>
          </tr>
          <tr v-for="(item, index) in items" :key="item.id" class="odd:bg-slate-950/30">
            <td class="border border-slate-800 px-2 py-2 text-center">{{ index + 1 }}</td>
            <td class="border border-slate-800 px-2 py-2 text-center">{{ item.nama }}</td>
            <td class="border border-slate-800 px-2 py-2 text-center">{{ item.telepon || '-' }}</td>
            <td class="border border-slate-800 px-2 py-2 text-center">{{ item.email || '-' }}</td>
            <td class="border border-slate-800 px-2 py-2">
              <div class="flex flex-wrap justify-center gap-2">
                <button
                  class="rounded-lg border border-slate-700 px-2 py-1 text-[10px] text-slate-200 hover:border-slate-500"
                  @click="startEdit(item)"
                  aria-label="Edit"
                  title="Edit"
                >
                  <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M4 13.5V16h2.5l7.1-7.1-2.5-2.5L4 13.5z"></path>
                    <path d="M14.7 3.3a1 1 0 0 1 1.4 0l1.6 1.6a1 1 0 0 1 0 1.4l-1.1 1.1-2.5-2.5 1.1-1.1z"></path>
                  </svg>
                </button>
                <button
                  class="rounded-lg border border-rose-500/40 px-2 py-1 text-[10px] text-rose-200 hover:border-rose-400"
                  @click="remove(item.id)"
                  aria-label="Hapus"
                  title="Hapus"
                >
                  <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6 7h8l-1 9H7L6 7z"></path>
                    <path d="M8 4h4l1 1h3v2H4V5h3l1-1z"></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { getErrorMessage } from '../utils/errors';
import { createAnggota, deleteAnggota, listAnggota, updateAnggota } from '../services/anggota';

const items = ref([]);
const loading = ref(false);
const error = ref(null);
const editingId = ref(null);

const form = ref({
  nama: '',
  telepon: '',
  email: '',
});

const isEditing = computed(() => editingId.value !== null);

const load = async () => {
  loading.value = true;
  error.value = null;
  try {
    items.value = await listAnggota();
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal memuat data anggota.');
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  editingId.value = null;
  form.value = {
    nama: '',
    telepon: '',
    email: '',
  };
};

const startEdit = (item) => {
  editingId.value = item.id;
  form.value = {
    nama: item.nama,
    telepon: item.telepon || '',
    email: item.email || '',
  };
};

const cancelEdit = () => {
  resetForm();
};

const submit = async () => {
  error.value = null;
  try {
    if (editingId.value) {
      await updateAnggota(editingId.value, { ...form.value });
    } else {
      await createAnggota({ ...form.value });
    }
    resetForm();
    await load();
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal menyimpan anggota.');
  }
};

const remove = async (id) => {
  if (!confirm('Hapus anggota ini?')) return;
  try {
    await deleteAnggota(id);
    await load();
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal menghapus anggota.');
  }
};

onMounted(load);
</script>
