let counter = document.getElementById("counter_item_cart")
let add = document.getElementById("add_one_cart_item") // adicionar mais 1
let remove = document.getElementById("remove_one_cart_item") // remove mais 1
let value_unique = document.getElementById("counter_value_unique") // Valor total
let subtotal = document.getElementById("subtotal_cart") // carrinho inteiro 
let frete = document.getElementById("frete")
let discount = document.getElementById("discount") //carrinho inteiro
let total = document.getElementById("total")




//adicionar novo 
function append_one_item(req){
    if (req == 1){
        let varia = Number(counter.getAttribute("valor"));
        varia -= 1;
        counter.setAttribute("valor", varia)
        counter.innerHTML = varia;
        
    }
}

function add_one_item(req){
    if (req == 1){
        let varia = Number(counter.getAttribute("valor"));
        varia += 1;
        counter.setAttribute("valor", varia)
        counter.innerHTML = varia;
    }
}

function value_total_item(){
    let varia = Number(value_unique.getAttribute('valor'))
    alert(value_unique)
}

