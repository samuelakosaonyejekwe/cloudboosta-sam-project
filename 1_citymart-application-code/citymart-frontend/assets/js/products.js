// cache-bust 1773316013
const statusEl = document.getElementById("status");
const gridEl = document.getElementById("grid");
const searchEl = document.getElementById("search");

let allProducts = [];

function money(n) {
  return new Intl.NumberFormat(undefined, {
    style: "currency",
    currency: "USD"
  }).format(Number(n || 0));
}

function esc(x) {
  return String(x ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;");
}

function addToCart(id) {
  const cart = JSON.parse(localStorage.getItem("citymart_cart") || "[]");
  const existing = cart.find(i => String(i.product_id) === String(id));

  if (existing) {
    existing.qty += 1;
  } else {
    cart.push({ product_id: id, qty: 1 });
  }

  localStorage.setItem("citymart_cart", JSON.stringify(cart));
  alert("Added to cart");
}

function getImageUrl(product) {
  const raw = String(product.image_url || "").trim();
  if (raw) return raw;
  return "/assets/img/placeholder-product.png";
}

function render(products) {
  gridEl.innerHTML = "";

  if (!products.length) {
    statusEl.textContent = "No products found.";
    return;
  }

  statusEl.textContent = "";

  for (const p of products) {
    const card = document.createElement("div");
    card.className = "product";

    card.innerHTML = `
      <img src="${getImageUrl(p)}" alt="Product"
           onerror="this.onerror=null;this.src='/assets/img/placeholder-product.png';" />
      <div class="product-body">
        <div class="product-name">${esc(p.name)}</div>
        <div class="price">${money(p.price)}</div>
        <p class="product-desc">${esc(p.description || "")}</p>
        <div class="product-actions">
          <a class="btn btn-secondary" href="./product.html?id=${encodeURIComponent(p.id)}">View</a>
          <button class="btn" data-id="${esc(p.id)}">Add</button>
        </div>
      </div>
    `;

    gridEl.appendChild(card);

    const btn = card.querySelector("button[data-id]");
    if (btn) {
                  btn.addEventListener("click", () => { if (typeof addToCart === "function") addToCart(p.id); });
    }
  }
}

function applySearch() {
  const q = (searchEl?.value || "").trim().toLowerCase();

  const filtered = allProducts.filter(p =>
    String(p.name || "").toLowerCase().includes(q) ||
    String(p.description || "").toLowerCase().includes(q)
  );

  render(filtered);
}

(async function init() {
  try {
    const res = await apiGet("/api/products.php");

    allProducts =
      (Array.isArray(res?.data?.products) && res.data.products) ||
      (Array.isArray(res?.products) && res.products) ||
      (Array.isArray(res?.data) && res.data) ||
      [];

    render(allProducts);

    if (searchEl) {
      searchEl.addEventListener("input", applySearch);
    }
  } catch (e) {
    statusEl.textContent = "Failed to load products. " + (e.message || e);
  }
})();
