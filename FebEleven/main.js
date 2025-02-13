document.addEventListener("DOMContentLoaded", function() {
    const productS = [
        { 
            Name: "Coke",
            Unit: "can",
            Price: 60,
            Qty: 8,
            Paid: true,
        },
        { 
            Name: "Royal",
            Unit: "bottle",
            Price: 60,
            Qty: 8,
            Paid: false,
        },
        { 
            Name: "Sprite",
            Unit: "can",
            Price: 60,
            Qty: 8,
            Paid: false,
        },
        { 
            Name: "Mountain Dew",
            Unit: "bottle",
            Price: 60,
            Qty: 8,
            Paid: true,
        },
    ];

    function displayProducts() {
        let tableBody = document.querySelector("#productTableBody");

        if (!tableBody) {
            console.error("Error: Table body not found!");
            return;
        }

        // Clear previous rows (if function is called multiple times)
        tableBody.innerHTML = "";

        productS.forEach(item => {
            let row = document.createElement("tr");

            // Calculate total price
            let total = item.Price * item.Qty;

            // Determine payment status
            let paymentStatus = item.Paid ? "Paid" : "Unpaid";
            
            // if (!item.Paid){
            //     row.style.backgroundColor = "green"
            // } else{
            //     row.style.backgroundColor = "red"
            // }
            row.innerHTML = `
                <td>${item.Name}</td>
                <td>${item.Unit}</td>
                <td>${item.Price}</td>
                <td>${item.Qty}</td>
                <td>${total}</td>
                <td>${paymentStatus}</td>
            `;

            if (!item.Paid) {
                row.classList.add("table-danger"); // Red for unpaid
            } else {
                row.classList.add("table-success"); // Green for paid
            }

            tableBody.appendChild(row);
        });

        console.log("Products successfully added to table.");
    }

    // Call the function inside DOMContentLoaded
    displayProducts();
});
