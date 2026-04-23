<template>
  <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-lg font-semibold text-white">Daftar User</h2>
        <p class="mt-1 text-sm text-slate-400">Buat akun untuk login ke sistem.</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <button
          class="w-full rounded-lg border border-slate-700 px-3 py-1 text-sm text-slate-300 hover:border-slate-500 sm:w-auto"
          @click="load"
        >
          Refresh
        </button>
        <button
          class="w-full rounded-lg bg-emerald-500 px-3 py-1 text-sm font-semibold text-slate-950 hover:bg-emerald-400 sm:w-auto"
          @click="openCreate"
        >
          Tambah User
        </button>
      </div>
    </div>

    <div v-if="error" class="mt-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </div>

    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-800">
      <table class="min-w-[700px] w-full border-collapse text-[11px] sm:text-xs">
        <thead class="bg-slate-900/80 text-slate-200">
          <tr>
            <th class="border border-slate-800 px-2 py-2 text-center">No</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Nama</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Email</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Role</th>
            <th class="border border-slate-800 px-2 py-2 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td class="border border-slate-800 px-3 py-2 text-center text-slate-400" colspan="5">Memuat data...</td>
          </tr>
          <tr v-else-if="items.length === 0">
            <td class="border border-slate-800 px-3 py-2 text-center text-slate-400" colspan="5">Belum ada user.</td>
          </tr>
          <tr v-for="(item, index) in items" :key="item.id" class="odd:bg-slate-950/30">
            <td class="border border-slate-800 px-2 py-2 text-center">{{ index + 1 }}</td>
            <td class="border border-slate-800 px-2 py-2 text-center">{{ item.name }}</td>
            <td class="border border-slate-800 px-2 py-2 text-center">{{ item.email }}</td>
            <td class="border border-slate-800 px-2 py-2 text-center">
              <span class="inline-flex justify-center rounded-full border border-slate-700 px-2 py-0.5 text-[10px] text-slate-200">
                {{ item.role }}
              </span>
            </td>
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

    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4"
      @click.self="closeModal"
    >
      <div class="w-full max-w-xl rounded-2xl border border-slate-800 bg-slate-950 p-5 shadow-2xl">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h3 class="text-lg font-semibold text-white">
              {{ isEditing ? 'Edit User' : 'Tambah User' }}
            </h3>
            <p class="mt-1 text-sm text-slate-400">
              {{ isEditing ? 'Perbarui data user di sini.' : 'Buat akun baru dari modal ini.' }}
            </p>
          </div>
          <button
            type="button"
            class="rounded-lg border border-slate-700 px-3 py-1 text-sm text-slate-200 hover:border-slate-500"
            @click="closeModal"
          >
            Tutup
          </button>
        </div>

        <form class="mt-5 grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
          <label class="grid gap-2 text-sm text-slate-300 sm:col-span-2">
            Nama
            <input v-model="form.name" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" required />
          </label>
          <label class="grid gap-2 text-sm text-slate-300">
            Email
            <input v-model="form.email" type="email" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" required />
          </label>
          <label class="grid gap-2 text-sm text-slate-300">
            Nomor HP
            <input v-model="form.telepon" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" />
          </label>
          <label class="grid gap-2 text-sm text-slate-300">
            Password
            <input
              v-model="form.password"
              type="password"
              class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2"
              :required="!isEditing"
            />
          </label>
          <label class="grid gap-2 text-sm text-slate-300">
            Role
            <select v-model="form.role" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2">
              <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
            </select>
          </label>

          <div class="sm:col-span-2 flex flex-wrap gap-3 border-t border-slate-800 pt-4">
            <button
              class="w-full rounded-lg bg-emerald-500 px-5 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 sm:w-auto"
            >
              {{ isEditing ? 'Simpan Perubahan' : 'Tambah User' }}
            </button>
            <button
              type="button"
              class="w-full rounded-lg border border-slate-700 px-5 py-2 text-sm text-slate-200 hover:border-slate-500 sm:w-auto"
              @click="closeModal"
            >
              Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { getErrorMessage } from '../utils/errors';
import { createUser, deleteUser, listUsers, updateUser } from '../services/users';

const props = defineProps({
  currentUser: {
    type: Object,
    default: null,
  },
});

const items = ref([]);
const loading = ref(false);
const error = ref(null);
const editingId = ref(null);
const showModal = ref(false);

const roles = computed(() => {
  if (props.currentUser?.role === 'admin') {
    return ['admin', 'user'];
  }

  return ['superadmin', 'admin', 'user'];
});

const form = ref({
  name: '',
  email: '',
  telepon: '',
  password: '',
  role: 'user',
});

const isEditing = computed(() => editingId.value !== null);

const load = async () => {
  loading.value = true;
  error.value = null;
  try {
    items.value = await listUsers();
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal memuat data user.');
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  editingId.value = null;
  form.value = {
    name: '',
    email: '',
    telepon: '',
    password: '',
    role: 'user',
  };
};

const openCreate = () => {
  resetForm();
  showModal.value = true;
};

const startEdit = (item) => {
  if (props.currentUser?.role === 'admin' && item.role === 'superadmin') {
    error.value = 'Admin tidak dapat mengubah akun superadmin.';
    return;
  }

  editingId.value = item.id;
  form.value = {
    name: item.name,
    email: item.email,
    telepon: item.telepon || '',
    password: '',
    role: item.role,
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

const submit = async () => {
  error.value = null;
  try {
    const payload = { ...form.value };

    if (props.currentUser?.role === 'admin' && form.value.role === 'superadmin') {
      error.value = 'Admin hanya dapat membuat akun admin atau user.';
      return;
    }

    if (editingId.value && !String(payload.password || '').trim()) {
      delete payload.password;
    }

    if (editingId.value) {
      await updateUser(editingId.value, payload);
    } else {
      await createUser(payload);
    }
    closeModal();
    await load();
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal menyimpan user.');
  }
};

const remove = async (id) => {
  if (!confirm('Hapus user ini?')) return;
  try {
    const target = items.value.find((item) => item.id === id);
    if (props.currentUser?.role === 'admin' && target?.role === 'superadmin') {
      error.value = 'Admin tidak dapat menghapus akun superadmin.';
      return;
    }

    await deleteUser(id);
    await load();
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal menghapus user.');
  }
};

onMounted(load);
</script>
