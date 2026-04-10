<template>
  <div class="mx-auto w-full max-w-md rounded-2xl border border-slate-800 bg-slate-950/40 p-6">
    <p class="text-sm text-slate-400">
      Silakan login untuk mengakses dashboard.
    </p>

    <div v-if="error" class="mt-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-200">
      {{ error }}
    </div>

    <form class="mt-4 grid gap-4" @submit.prevent="submitLogin">
      <label class="grid gap-2 text-sm text-slate-300">
        Email
        <input v-model="loginForm.email" type="email" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" required />
      </label>
      <label class="grid gap-2 text-sm text-slate-300">
        Password
        <input v-model="loginForm.password" type="password" class="rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2" required />
      </label>
      <button class="rounded-lg bg-emerald-500 px-4 py-2 font-medium text-slate-950 transition hover:bg-emerald-400">
        Masuk
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
  error: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['login']);

const loginForm = ref({
  email: '',
  password: '',
});

const submitLogin = () => {
  emit('login', { ...loginForm.value });
};
</script>
