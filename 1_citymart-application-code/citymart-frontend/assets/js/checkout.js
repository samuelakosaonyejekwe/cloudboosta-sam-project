const cartEl = document.getElementById("cart");
const statusEl = document.getElementById("status");
const form = document.getElementById("orderForm");

function getCart() {
  return JSON.parse(localStorage.getItem("citymart_cart") || "[]");
}
function setCart(cart) {
  localStorage.setItem("citymart_cart", JSON.stringify(cart));
}
function clearCart() {
  setCart([]);
}

function renderCart(cart) {
  if (!cart.length) {
    cartEl.innerHTML = `
      <p class="muted">Your cart is empty.</p>
      <a class="btn" href="./products.html">Browse Products</a>
    `;
    return;
  }

  const rows = cart.map(i => `
    <div class="row row-space" style="padding:8px 0;border-bottom:1px solid #e5e7eb;">
      <div><strong>Product ID:</strong> ${escapeHtml(i.product_id)}</div>
      <div><strong>Qty:</strong> ${escapeHtml(i.qty)}</div>
    </div>
  `).join("");

  cartEl.innerHTML = `
    <h2>Cart</h2>
    ${rows}
    <p class="muted small">This lab cart stores only product IDs and quantities.</p>
  `;
}

function escapeHtml(s) {
  return String(s ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

const cart = getCart();
renderCart(cart);

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const cart = getCart();
  if (!cart.length) {
    statusEl.textContent = "Cart is empty. Add products first.";
    return;
  }

  statusEl.textContent = "Submitting order request…";

  const payload = {
    customer_name: document.getElementById("name").value.trim(),
    customer_email: document.getElementById("email").value.trim(),
    customer_phone: document.getElementById("phone").value.trim(),
    notes: document.getElementById("notes").value.trim(),
    items: cart.map(i => ({ product_id: i.product_id, qty: Number(i.qty) }))
  };

  try {
    const data = await apiPost("/api/order.php", payload);
    clearCart();
    const ref = encodeURIComponent(data.order_ref || "");
    window.location.href = "./order-success.html?ref=" + ref;
  } catch (err) {
    statusEl.textContent = "Failed to submit order. " + err.message;
  }
});
