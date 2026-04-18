<template>
  <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-semibold text-white">{{ isEditing ? 'Edit Kegiatan' : 'Buat Kegiatan' }}</h2>
        <p class="mt-1 text-sm text-slate-400">Lengkapi form berikut lalu simpan.</p>
      </div>
      <button
        class="rounded-lg border border-slate-700 px-3 py-1 text-sm text-slate-300 hover:border-slate-500"
        @click="emit('cancel')"
      >
        Kembali
      </button>
    </div>

    <div v-if="error" class="mt-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </div>

    <form class="mt-6 grid gap-4 sm:gap-5 md:grid-cols-2" @submit.prevent="submit">
      <label class="grid gap-2 text-sm text-slate-300">
        Lokasi/OPD
        <div class="relative" ref="opdDropdownRef">
          <button
            type="button"
            class="flex w-full items-center justify-between rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2 text-left text-sm text-slate-100"
            @click="toggleOpdDropdown"
          >
            <span v-if="form.lokasi_opd" class="truncate">{{ form.lokasi_opd }}</span>
            <span v-else class="text-slate-500">Pilih OPD</span>
            <span class="text-slate-500">v</span>
          </button>
          <div
            v-if="opdOpen"
            class="absolute z-10 mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/95 p-2 text-sm"
          >
            <input
              v-model="opdSearch"
              class="mb-2 w-full rounded-md border border-slate-700 bg-slate-900/70 px-2 py-1 text-xs text-slate-100 placeholder:text-slate-500"
              placeholder="Cari OPD..."
              type="text"
            />
            <div class="max-h-56 overflow-auto">
              <div v-if="opdLoading" class="px-2 py-1 text-slate-400">Memuat OPD...</div>
              <div v-else-if="filteredOpdOptions.length === 0" class="px-2 py-1 text-slate-400">
                OPD tidak ditemukan.
              </div>
              <button
                v-for="opd in filteredOpdOptions"
                :key="opd.id"
                type="button"
                class="flex w-full items-center gap-2 rounded-md px-2 py-1 text-left text-slate-100 hover:bg-slate-800/60"
                @click="selectOpd(opd)"
              >
                <span>
                  {{ opd.nama }}{{ opd.lokasi ? ` - ${opd.lokasi}` : '' }}
                </span>
              </button>
            </div>
          </div>
        </div>
      </label>
      <label class="grid gap-2 text-sm text-slate-300">
        Jenis Kegiatan
        <input v-model="form.jenis_gangguan" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" required />
      </label>
      <label class="grid gap-2 text-sm text-slate-300">
        Tanggal Gangguan & Jam
        <input v-model="form.tanggal_gangguan" type="datetime-local" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" />
      </label>
      <label class="grid gap-2 text-sm text-slate-300">
        Status
        <select
          v-model="form.status"
          class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2"
          :style="statusDotStyle"
        >
          <option v-for="status in statusOptions" :key="status" :value="status">
            {{ status }}
          </option>
        </select>
      </label>
      <label class="grid gap-2 text-sm text-slate-300">
        Mulai Pengerjaan & Jam
        <input v-model="form.mulai_pengerjaan" type="datetime-local" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" />
      </label>
      <label class="grid gap-2 text-sm text-slate-300">
        Selesai Pekerjaan & Jam
        <input v-model="form.selesai_pengerjaan" type="datetime-local" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" />
      </label>
      <label class="grid gap-2 text-sm text-slate-300">
        Kendala
        <input v-model="form.kendala" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" />
      </label>
      <label class="grid gap-2 text-sm text-slate-300">
        Tindak Lanjut/Solusi
        <input v-model="form.tindak_lanjut" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" />
      </label>
      <label class="grid gap-2 text-sm text-slate-300">
        Tim Bertugas (bisa lebih dari 1)
        <div class="relative" ref="dropdownRef">
          <button
            type="button"
            class="flex w-full items-center justify-between rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2 text-left text-sm text-slate-100 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="isTimLocked"
            @click="toggleDropdown"
          >
            <span v-if="form.tim_bertugas.length" class="truncate">
              {{ form.tim_bertugas.map(formatTeamMember).join(', ') }}
            </span>
            <span v-else class="text-slate-500">
              {{ isTimLocked ? 'Tim terkunci saat status belum dikerjakan' : 'Pilih anggota' }}
            </span>
            <span class="text-slate-500">v</span>
          </button>
          <div
            v-if="anggotaOpen"
            class="absolute z-10 mt-2 max-h-56 w-full overflow-auto rounded-lg border border-slate-700 bg-slate-950/95 p-2 text-sm"
          >
            <div v-if="anggotaLoading" class="px-2 py-1 text-slate-400">Memuat anggota...</div>
            <div v-else-if="anggotaOptions.length === 0" class="px-2 py-1 text-slate-400">Belum ada anggota.</div>
              <label
              v-for="anggota in anggotaOptions"
              :key="anggota.id"
              class="flex items-center gap-2 rounded-md px-2 py-1 hover:bg-slate-800/60"
            >
              <input
                type="checkbox"
                class="h-4 w-4 rounded border-slate-600 bg-slate-900/60"
                :disabled="isTimLocked"
                :checked="isSelected(anggota)"
                @change="toggleSelection(anggota)"
              />
              <span>{{ anggota.nama }}</span>
            </label>
          </div>
        </div>
        <div v-if="form.tim_bertugas.length" class="mt-2 flex flex-wrap gap-1">
          <span
            v-for="name in form.tim_bertugas"
            :key="name"
            class="rounded-full border border-slate-700 px-2 py-0.5 text-xs text-slate-200"
          >
            {{ formatTeamMember(name) }}
          </span>
        </div>
      </label>
      <label class="grid gap-2 text-sm text-slate-300">
        Keterangan
        <input v-model="form.keterangan" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" />
      </label>

      <div class="md:col-span-2 flex flex-wrap gap-3 border-t border-slate-800 pt-4">
        <button
          class="rounded-lg bg-emerald-500 px-5 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400"
        >
          Simpan
        </button>
        <button
          type="button"
          class="rounded-lg border border-slate-700 px-5 py-2 text-sm text-slate-200 hover:border-slate-500"
          @click="emit('cancel')"
        >
          Batal
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { getErrorMessage } from '../utils/errors';
import { createGangguan, updateGangguan } from '../services/kegiatan-jaringan';
import { listAnggota } from '../services/anggota';
import { listOpd } from '../services/opd';
import { toDateTimeLocalValue } from '../utils/datetime';

const props = defineProps({
  initialData: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['saved', 'cancel']);

const error = ref(null);
const statusOptions = ['BELUM DIKERJAKAN', 'PROSES', 'SELESAI'];
const anggotaOptions = ref([]);
const anggotaLoading = ref(false);
const anggotaOpen = ref(false);
const dropdownRef = ref(null);
const opdDropdownRef = ref(null);
const opdOptions = ref([]);
const opdLoading = ref(false);
const opdOpen = ref(false);
const opdSearch = ref('');

const form = ref({
  tanggal_gangguan: '',
  lokasi_opd: '',
  jenis_gangguan: '',
  mulai_pengerjaan: '',
  selesai_pengerjaan: '',
  kendala: '',
  tindak_lanjut: '',
  tim_bertugas: [],
  status: 'BELUM DIKERJAKAN',
  keterangan: '',
});

const isEditing = computed(() => Boolean(props.initialData && props.initialData.id));
const isTimLocked = computed(() => form.value.status === 'BELUM DIKERJAKAN');

const hydrateForm = (item) => {
  const timBertugas = item?.tim_bertugas
    ? item.tim_bertugas.split(',').map((name) => name.trim()).filter(Boolean)
    : [];
  form.value = {
    tanggal_gangguan: toDateTimeLocalValue(item?.tanggal_gangguan),
    lokasi_opd: item?.lokasi_opd || '',
    jenis_gangguan: item?.jenis_gangguan || '',
    mulai_pengerjaan: toDateTimeLocalValue(item?.mulai_pengerjaan),
    selesai_pengerjaan: toDateTimeLocalValue(item?.selesai_pengerjaan),
    kendala: item?.kendala || '',
    tindak_lanjut: item?.tindak_lanjut || '',
    tim_bertugas: timBertugas,
    status: item?.status || 'BELUM DIKERJAKAN',
    keterangan: item?.keterangan || '',
  };
};

watch(
  () => props.initialData,
  (value) => {
    hydrateForm(value);
  },
  { immediate: true }
);

const loadAnggota = async () => {
  anggotaLoading.value = true;
  try {
    anggotaOptions.value = await listAnggota();
  } catch (err) {
    anggotaOptions.value = [];
  } finally {
    anggotaLoading.value = false;
  }
};

const loadOpd = async () => {
  opdLoading.value = true;
  try {
    opdOptions.value = await listOpd();
  } catch (err) {
    opdOptions.value = [];
  } finally {
    opdLoading.value = false;
  }
};

const toggleDropdown = () => {
  if (isTimLocked.value) return;
  anggotaOpen.value = !anggotaOpen.value;
};

const toggleOpdDropdown = () => {
  opdOpen.value = !opdOpen.value;
  if (opdOpen.value) {
    opdSearch.value = '';
  }
};

const selectOpd = (opd) => {
  form.value.lokasi_opd = `${opd.nama}${opd.lokasi ? ` - ${opd.lokasi}` : ''}`;
  opdOpen.value = false;
};

const teamMemberToken = (anggota) => `${anggota.nama}::${anggota.user_id ?? anggota.id}`;

const formatTeamMember = (value) => {
  const token = String(value || '').trim();
  if (!token) return '';

  if (token.includes('::')) {
    return token.split('::')[0].trim();
  }

  return token;
};

const filteredOpdOptions = computed(() => {
  const term = opdSearch.value.trim().toLowerCase();
  if (!term) return opdOptions.value;
  return opdOptions.value.filter((opd) => {
    const label = `${opd.nama} ${opd.lokasi || ''}`.toLowerCase();
    return label.includes(term);
  });
});

const isSelected = (anggota) => {
  const token = teamMemberToken(anggota);
  return form.value.tim_bertugas.includes(token) || form.value.tim_bertugas.includes(anggota.nama);
};

const toggleSelection = (anggota) => {
  if (isTimLocked.value) return;
  const current = [...form.value.tim_bertugas];
  const token = teamMemberToken(anggota);
  const index = current.indexOf(token);
  const legacyIndex = current.indexOf(anggota.nama);

  if (index >= 0) {
    current.splice(index, 1);
  } else if (legacyIndex >= 0) {
    current.splice(legacyIndex, 1);
  } else {
    current.push(token);
  }
  form.value.tim_bertugas = current;
};

const handleOutsideClick = (event) => {
  const clickedAnggota = dropdownRef.value && dropdownRef.value.contains(event.target);
  const clickedOpd = opdDropdownRef.value && opdDropdownRef.value.contains(event.target);
  if (!clickedAnggota) anggotaOpen.value = false;
  if (!clickedOpd) opdOpen.value = false;
};

const statusColor = (status) => {
  if (status === 'SELESAI') return '#22c55e';
  if (status === 'PROSES') return '#f59e0b';
  return '#ef4444';
};

const statusDotStyle = computed(() => {
  const color = statusColor(form.value.status);
  return {
    backgroundImage: `radial-gradient(circle, ${color} 45%, transparent 46%)`,
    backgroundRepeat: 'no-repeat',
    backgroundPosition: '0.75rem center',
    backgroundSize: '0.5rem 0.5rem',
    paddingLeft: '2rem',
  };
});

watch(
  () => form.value.status,
  (status) => {
    if (status === 'BELUM DIKERJAKAN') {
      anggotaOpen.value = false;
    }
  }
);

const submit = async () => {
  error.value = null;
  if (!form.value.lokasi_opd) {
    error.value = 'Lokasi/OPD wajib dipilih.';
    return;
  }
  try {
    const payload = {
      ...form.value,
      tim_bertugas: form.value.tim_bertugas.length ? form.value.tim_bertugas.join(', ') : null,
      tanggal_gangguan: form.value.tanggal_gangguan || null,
      mulai_pengerjaan: form.value.mulai_pengerjaan || null,
      selesai_pengerjaan: form.value.selesai_pengerjaan || null,
    };

    if (isEditing.value) {
      await updateGangguan(props.initialData.id, payload);
    } else {
      await createGangguan(payload);
    }

    emit('saved');
  } catch (err) {
    error.value = getErrorMessage(err, 'Gagal menyimpan kegiatan.');
  }
};

onMounted(() => {
  loadAnggota();
  loadOpd();
  document.addEventListener('click', handleOutsideClick);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleOutsideClick);
});
</script>
