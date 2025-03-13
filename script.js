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
        // let gate1 = [0,229,729,200];
        // let gate2 = [752,229,729,952];


        // const condition1 = rect.left < gate1[2]  && rect.right > gate1[1]  &&
        // rect.top < gate1[3] && rect.bottom > gate1[0]

        // const condition2 = rect.left < gate2[2]  && rect.right > gate2[1]  &&
        // rect.top < gate2[3] && rect.bottom > gate2[0]
        // if(condition1 || condition2)
        // console.log(`Pozíció: Top ${Math.round(rect.top)}px, Left ${Math.round(rect.left)}px,Right ${Math.round(rect.right)}px,Bottom ${Math.round(rect.bottom)}px`)
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


