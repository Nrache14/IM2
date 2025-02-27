$.ajax({
    url: "products.php",
    // method: "GET"
}).done(function(data) {
    console.log(data);
    try {
        let result = JSON.parse(data);

        if (!Array.isArray(result)) {
            throw new Error("Invalid JSON format: Expected an array");
        }

        let template = document.querySelector("#productRowTemplate");
        let parent = document.querySelector("#tableBody");

        result.forEach(item => {  
            let clone = template.content.cloneNode(true);
            clone.querySelector(".tdID").innerHTML = item.prod_Id;
            clone.querySelector(".tdName").innerHTML = item.prod_name;
            parent.appendChild(clone);
        });

    } catch (error) {
        console.error("Error parsing JSON:", error);
    }
});

// var x = document.querySelector("h1").innerHTML = "asdads";
// console.log(x);


$("#btnCreateProduct").click(function(){
    $.ajax({
        url:"create.php",
        type: "GET",
        dataType:"json",
        data: {
            pname:"Apple"
        }
    }).done(function(result){
        
        if (result.res == "success") {
            alert("Click OK to add product");
            window.location.reload();
        } else {
            alert("Error");
        }
    })
});

$("#btnUpdateProduct").click(function(){
    $.ajax({
        url:"update.php",
        type: "GET",
        dataType:"json",
        data: {
            id: 3,
            pname:"TNT",
        }
    }).done(function(result){
        
        if (result.res == "success") {
            alert("Click OK to update");
            window.location.reload();
        } else {
            alert("Error");
        }
    })
});

$("#btnDeleteProduct").click(function(){
    $.ajax({
        url:"delete.php",
        type: "GET",
        dataType:"json",
        data: {
            id: 14
        }
    }).done(function(result){
        
        if (result.res == "success") {
            alert("Click OK to confirm deletion");
            window.location.reload();
        } else {
            alert("Error");
        }
    })
});