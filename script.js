const socket = new WebSocket("ws://localhost:8080");
let id;
socket.onopen = () => {
    console.log("Connected to WebSocket server");
};

socket.onmessage = (event) => {
    const data = JSON.parse(event.data);

    if(data.type ==="data_update"){
        document.getElementById('clientID').innerHTML = data.id
        id = data.id
    }
    if(data.type === "shape_update"){
        const container = document.getElementById("shapeContainer");    
        container.innerHTML = ""
        console.log(data)
        data.shapes.map((item)=>generateShape(item.shape,item.id,item.position))
    }

    if(data.type ==="user_disconnected"){
        document.getElementById('modal').innerHTML=`${data.user_id} disconnected`
        deleteShape(data.user_id)
    }
    if(data.type ==="shape_movement"){
        console.log(data)
    }

};

socket.onclose = () => {
    console.log("Disconnected from server");
};




function deleteShape(user_id){
    const element = document.getElementById(`${user_id}`);  
    element.remove()
}


function generateShape(shapeName, userId,position) {
    const container = document.getElementById("shapeContainer");
    const element = document.createElement("div");

    element.id = userId;
    element.style.position = "absolute";
    
    element.style.top=`${position[0]}px`
    element.style.left=`${position[1]}px`
    
    if (shapeName === "triangle") {
        element.className = "triangle";
    }
    
    container.appendChild(element);
    element.innerHTML = userId;
    // makeDraggable(element);


    
}



function makeDraggable(element) {
    let offsetX, offsetY;
    element.onmousedown = function(e) {

        if(id === Number(e.target.id)){
            e.preventDefault();
            offsetX = e.clientX - element.offsetLeft;
            offsetY = e.clientY - element.offsetTop;
            document.onmouseup = closeDrag;
            document.onmousemove = dragElement;
        }
    };
    
    function dragElement(e) {
        e.preventDefault();
        element.style.top = (e.clientY - offsetY) + "px";
        element.style.left = (e.clientX - offsetX) + "px";
        let top = e.clientY - offsetY
        let left = e.clientX - offsetX

    }
    
    function closeDrag() {
        document.onmouseup = null;
        document.onmousemove = null;
    }
}

