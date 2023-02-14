function equalizeClickCoordinates(x, y) {
    const standardScreenWidth = 1366;
    const standardScreenHeight = 625;
    const currentScreenWidth = window.innerWidth;
    const currentScreenHeight = window.innerHeight;

    const xRatio = currentScreenWidth / standardScreenWidth;
    const yRatio = currentScreenHeight / standardScreenHeight;

    return {
        x: x / xRatio,
        y: y / yRatio
    };
}


async function captureDados() {
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        return;
    }
    const canvas = document.createElement("canvas");
    canvas.id = "capture";
    canvas.width = document.body.scrollWidth;
    canvas.height = document.body.scrollHeight;

    await html2canvas(document.body, {
        canvas,
        onclone(cloneDoc) {
            if (!cloneDoc.getElementById("capture")) {
                const canvas = cloneDoc.createElement("canvas");
                canvas.id = "capture";
                canvas.width = document.body.scrollWidth;
                canvas.height = document.body.scrollHeight;
                cloneDoc.body.appendChild(canvas);
            }
            cloneDoc.getElementById("capture").style.display = "none";
        },
    });

    const imageData = canvas.toDataURL();
    await postData({ imageData: imageData, url: window.location.href, type: "image" });

    document.addEventListener("click", async(event) => {
        const equalizedClickCoordinates = equalizeClickCoordinates(event.clientX, event.clientY);
        const click = {
            x: equalizedClickCoordinates.x,
            y: equalizedClickCoordinates.y,
            dateTime: formatDateTime(new Date()),
            location: await getCurrentLocation(),
        };

        localStorage.setItem('clicks', JSON.stringify([...JSON.parse(localStorage.getItem('clicks') || '[]'), click]));
    });

    let mouseMovements = [];

    document.addEventListener("mousemove", async(event) => {
        const equalizedMoveCoordinates = equalizeClickCoordinates(event.clientX, event.clientY);
        const mouseMovement = {
            x: equalizedMoveCoordinates.x,
            y: equalizedMoveCoordinates.y,
            dateTime: formatDateTime(new Date()),
            location: await getCurrentLocation(),
        };

        mouseMovements.push(mouseMovement);
        localStorage.setItem('movements', JSON.stringify([...JSON.parse(localStorage.getItem('movements') || '[]'), mouseMovement]));
    });

    let postDataInterval;
    if (postDataInterval) {
        clearInterval(postDataInterval);
    }
    postDataInterval = setInterval(async() => {
        if (!localStorage.getItem('clicks') && !localStorage.getItem('movements')) {
            return;
        }

        await postData({
            clicks: JSON.parse(localStorage.getItem('clicks')),
            movements: JSON.parse(localStorage.getItem('movements')),
            url: window.location.href,
            type: "data"
        });
        localStorage.removeItem('clicks');
        localStorage.removeItem('movements');
    }, 20000);

}

async function postData(data) {
    const response = await fetch("DataReceiver.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
    });
    return response.json();
}

function formatDateTime(date) {
    return `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`;
}

async function getCurrentLocation() {
    return new Promise((resolve) => {
        navigator.geolocation.getCurrentPosition((position) => {
            resolve(`${position.coords.latitude}, ${position.coords.longitude}`);
        }, () => {
            // O usuário não permitiu o compartilhamento de sua localização
            resolve(null);
        });
    });
}