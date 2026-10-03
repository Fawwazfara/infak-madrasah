// Util tanggal: format tampilan dd/mm/yyyy (supaya tidak tergantung locale browser)

// ISO (yyyy-mm-dd) -> tampilan (dd/mm/yyyy)
export const toDisplayDate = (iso) => {
  if (!iso) return '';
  const parts = String(iso).slice(0, 10).split('-');
  if (parts.length !== 3) return '';
  const [y, m, d] = parts;
  return `${d}/${m}/${y}`;
};

// Tampilan (dd/mm/yyyy atau dd-mm-yyyy) -> ISO (yyyy-mm-dd). Balik '' jika tidak valid.
export const toIsoDate = (display) => {
  if (!display) return '';
  const match = /^(\d{1,2})[/\-.](\d{1,2})[/\-.](\d{4})$/.exec(String(display).trim());
  if (!match) return '';
  const d = parseInt(match[1], 10);
  const m = parseInt(match[2], 10);
  const y = parseInt(match[3], 10);
  if (m < 1 || m > 12 || d < 1 || d > 31) return '';
  return `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
};

// Auto format saat mengetik: "1" -> "1/", "12" -> "12/", "123" -> "12/3", dst.
export const autoFormatDisplayDate = (value) => {
  if (!value) return '';
  const digits = String(value).replace(/\D/g, '').slice(0, 8);
  if (digits.length <= 2) return digits;
  if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
  return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
};
