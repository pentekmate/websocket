const socket = new WebSocket("ws://localhost:8080");
let id;
socket.onopen = () => {
    console.log("Connected to WebSocket server");
};

socket.onmessage = (event) => {
    const data = JSON.parse(event.data);
    if(data.type === "alert"){
        console.log(data)
    }
    if(data.type ==="data_update"){
        document.getElementById('clientID').innerHTML = data.id
        id = data.id
    }
    if(data.type === "shape_update"){
       console.log(data)
        if(data.userShapes){
            const container = document.getElementById("shapeContainer");    
            container.innerHTML = ""
            data.userShapes.map((item)=>generateShape(item.shape,item.id,item.position))
        }
    }

    if(data.type ==="user_disconnected"){
        if(data.userShapes){
        
        const container = document.getElementById("shapeContainer");    
        container.innerHTML = ""
        data.userShapes.map((item)=>generateShape(item.shape,item.id,item.position))
        }
    }
    if(data.type ==="shape_movement"){
        moveShape(data.id,data.position)
    }


    if(data.type === "shape_reset"){
        const element = document.getElementById(data.id);
    
        if (element) {
        
            const newElement = element.cloneNode(true);
            element.parentNode.replaceChild(newElement, element);
        }
        
        const container = document.getElementById("shapeContainer");    
        container.innerHTML = ""
        const timeOut = setTimeout(()=>{
            data.userShapes.map((item)=>generateShape(item.shape,item.id,item.position))
        },1000)
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
    
    element.className = `${shapeName}`

    container.appendChild(element);
    element.innerHTML = userId;
    makeDraggable(element);

    
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
        let rect = element.getBoundingClientRect()
        element.style.top = (e.clientY - offsetY) + "px";
        element.style.left = (e.clientX - offsetX) + "px";
        let top = e.clientY - offsetY
        let left = e.clientX - offsetX

        const update = {
            type:"update_shape_position",
            id:id,
            position:[top,left,rect.right,rect.bottom]
        }

        
        
        socket.send(JSON.stringify(update))
    }
    
    function closeDrag() {
        document.onmouseup = null;
        document.onmousemove = null;
    }
}

function moveShape(id,position){
    const element = document.getElementById(`${id}`)

    element.style.top=`${position[0]}px`
    element.style.left=`${position[1]}px`
}


function showPosition() {
    const div = document.getElementById("gate1");
    const rect = div.getBoundingClientRect(); // Lekéri az elem pozícióját

    const div2 = document.getElementById("gate2");
    const rect2 = div2.getBoundingClientRect(); // Lekéri az elem pozícióját

    document.getElementById("gate1").innerHTML = 
        `Pozíció: Top ${Math.round(rect.top)}px, Left ${Math.round(rect.left)}px,Right ${Math.round(rect.right)}px,Bottom ${Math.round(rect.bottom)}px`;

    document.getElementById("gate2").innerHTML = 
    `Pozíció: Top ${Math.round(rect2.top)}px, Left ${Math.round(rect2.left)}px,Right ${Math.round(rect2.right)}px,Bottom ${Math.round(rect2.bottom)}px,`;
}

showPosition()


