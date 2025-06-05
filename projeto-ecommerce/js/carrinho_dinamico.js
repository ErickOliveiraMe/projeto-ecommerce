// carrinho_dinamico.js

// Inicializar eventos para cada item
window.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".cart-item").forEach(item => {
    const counter = item.querySelector(".item-quantity p");
    const addBtn = item.querySelector(".quantity-btn.plus");
    const removeBtn = item.querySelector(".quantity-btn.minus");
    const valueDisplay = item.querySelector(".item-price p");
    const removeItemBtn = item.querySelector(".remove-item");

    addBtn.addEventListener("click", () => {
      updateItemQuantity(counter, valueDisplay, 1);
    });

    removeBtn.addEventListener("click", () => {
      updateItemQuantity(counter, valueDisplay, -1);
    });

    removeItemBtn.addEventListener("click", () => {
      item.remove();
      updateCartTotals();
    });
  });

  updateCartTotals();
});

function updateItemQuantity(counter, valueDisplay, change) {
  let quantity = Number(counter.getAttribute("valor"));
  quantity += change;

  if (quantity < 1) return;

  counter.setAttribute("valor", quantity);
  counter.innerText = quantity;

  const unitPrice = Number(valueDisplay.getAttribute("valor"));
  valueDisplay.innerText = `R$ ${(quantity * unitPrice).toFixed(2)}`;

  updateCartTotals();
}

function updateCartTotals() {
  const items = document.querySelectorAll(".cart-item");
  let subtotal = 0;

  items.forEach(item => {
    const counter = item.querySelector(".item-quantity p");
    const unitPrice = Number(item.querySelector(".item-price p").getAttribute("valor"));
    const quantity = Number(counter.getAttribute("valor"));
    subtotal += unitPrice * quantity;
  });

  const frete = 25.00; // Pode ser dinâmico futuramente
  const desconto = 0.00; // Pode ser ajustado conforme cupom etc

  const total = subtotal + frete - desconto;

  // Atualizar o DOM com os valores calculados
  document.getElementById("subtotal_cart").innerText = `R$ ${subtotal.toFixed(2)}`;
  document.getElementById("frete").innerText = `R$ ${frete.toFixed(2)}`;
  document.getElementById("discount").innerText = `-R$ ${desconto.toFixed(2)}`;
  document.getElementById("total").innerText = `R$ ${total.toFixed(2)}`;
}
