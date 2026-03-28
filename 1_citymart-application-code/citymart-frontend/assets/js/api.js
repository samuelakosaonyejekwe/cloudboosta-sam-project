async function apiGet(path) {
  const base = (window.CITYMART_CONFIG && window.CITYMART_CONFIG.API_BASE_URL) || "";
  const res = await fetch(base + path, { headers: { "Accept": "application/json" } });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw new Error(data.message || ("Request failed: " + res.status));
  return data;
}

async function apiPost(path, body) {
  const base = (window.CITYMART_CONFIG && window.CITYMART_CONFIG.API_BASE_URL) || "";
  const res = await fetch(base + path, {
    method: "POST",
    headers: { "Content-Type": "application/json", "Accept": "application/json" },
    body: JSON.stringify(body)
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw new Error(data.message || ("Request failed: " + res.status));
  return data;
}

function money(n) {
  const num = Number(n || 0);
  return new Intl.NumberFormat(undefined, { style: "currency", currency: "USD" }).format(num);
}
