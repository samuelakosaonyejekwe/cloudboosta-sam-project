const statusEl = document.getElementById("status");
const detailEl =
  document.getElementById("product") ||
  document.getElementById("product") ||
  document.getElementById("detail") ||
  document.getElementById("content");

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
  if (existing) existing.qty += 1;
  else cart.push({ product_id: id, qty: 1 });
  localStorage.setItem("citymart_cart", JSON.stringify(cart));
  alert("Added to cart");
}

function getImageUrl(product) {
  return String(product.image_url || "").trim() || "/assets/img/placeholder-product.png";
}

(async function init() {
  try {
    if (!detailEl) {
      if (statusEl) statusEl.textContent = "Product page container not found.";
      return;
    }

    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");

    if (!id) {
      if (statusEl) statusEl.textContent = "Missing product id.";
      return;
    }

    const res = await apiGet("/api/product.php?id=" + encodeURIComponent(id));
    const product = res?.data?.product || null;

    if (!product) {
      if (statusEl) statusEl.textContent = "Product not found.";
      return;
    }

    if (statusEl) statusEl.textContent = "";

    document.getElementById("pimg").src = getImageUrl(product);
    document.getElementById("pname").innerText = product.name;
    document.getElementById("pprice").innerText = money(product.price);
    document.getElementById("pdesc").innerText = product.description || "";
    detailEl.classList.remove("hidden");

    const btn = document.getElementById("add");
    if (btn) btn.addEventListener("click", () => addToCart(product.id));
  } catch (e) {
    if (statusEl) statusEl.textContent = "Failed to load product. " + (e.message || e);
  }
})();
