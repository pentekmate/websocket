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