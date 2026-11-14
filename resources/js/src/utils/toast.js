import { reactive } from 'vue';

export const toastState = reactive({
  items: [],
});

let nextId = 1;

export function notify(message, type = 'info') {
  const id = nextId++;
  toastState.items.push({ id, message, type });
  setTimeout(() => {
    dismissToast(id);
  }, 4500);
}

export function dismissToast(id) {
  const idx = toastState.items.findIndex((item) => item.id === id);
  if (idx !== -1) {
    toastState.items.splice(idx, 1);
  }
}

