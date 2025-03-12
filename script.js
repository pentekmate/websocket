const socket = new WebSocket("ws://localhost:8080");

socket.onopen = () => {
    console.log("Connected to WebSocket server");
};

socket.onmessage = (event) => {
    const data = JSON.parse(event.data);

    console.log(data)
    
    if(data.type === "shape_update"){
        data.shapes.map((item)=>generateShape(item.shape,item.id))
    }

    if(data.type ==="user_disconnected"){
    //   alert(`user ${data.user_id} disconnected`)
    }
    if (Array.isArray(data.data) && data.data.length === 2) {
        position = data.data;
        const elmnt = document.getElementById("mydiv");
        // Beállítjuk a kapott pozíciót
        elmnt.style.left = position[0] + "px";
        elmnt.style.top = position[1] + "px";
    }
};

socket.onclose = () => {
    console.log("Disconnected from server");
};

// Tegyük draggable-é az elemet
makeDraggable(document.getElementById("mydiv"));

function makeDraggable(element) {
    let posX = 0, posY = 0, mouseX = 0, mouseY = 0;
    const header = document.getElementById(element.id + "header") || element;

    header.onmousedown = dragMouseDown;

    function dragMouseDown(e) {
        e.preventDefault();
        mouseX = e.clientX;
        mouseY = e.clientY;
        document.onmouseup = closeDrag;
        document.onmousemove = dragElement;
    }

    function dragElement(e) {
        e.preventDefault();
        posX = mouseX - e.clientX;
        posY = mouseY - e.clientY;
        mouseX = e.clientX;
        mouseY = e.clientY;

        element.style.top = (element.offsetTop - posY) + "px";
        element.style.left = (element.offsetLeft - posX) + "px";

        // Küldjük az új pozíciót a szervernek
        if (socket.readyState === WebSocket.OPEN) {
            socket.send(JSON.stringify({type:"update_data", data: [element.offsetLeft, element.offsetTop] }));
        }
    }

    function closeDrag() {
        document.onmouseup = null;
        document.onmousemove = null;
    }
}

function generateShape(shapeName,user_id) {
    const container = document.getElementById("shapeContainer");
   
    
    const element = document.createElement("div");
    if (shapeName === "triangle") {
        element.className = "triangle";
    }
    
    container.appendChild(element);
    element.innerHTML = user_id
}

