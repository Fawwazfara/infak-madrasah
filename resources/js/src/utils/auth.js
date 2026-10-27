export function getStoredUser() {
  try {
    return JSON.parse(localStorage.getItem('user') || 'null');
  } catch (error) {
    return null;
  }
}

export function isReadOnlyUser() {
  return getStoredUser()?.role === 'viewer';
}
