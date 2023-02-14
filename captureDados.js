// Essa é uma função que normaliza as coordenadas de clique (x, y) para serem independentes da resolução da tela do usuário.
function equalizeClickCoordinates(x, y) {
    // Largura e altura padrão da tela
    const standardScreenWidth = 1366;
    const standardScreenHeight = 625;

    // Largura e altura da tela atual do usuário
    const currentScreenWidth = window.innerWidth;
    const currentScreenHeight = window.innerHeight;

    // Cálculo dos ratios de largura e altura
    const xRatio = currentScreenWidth / standardScreenWidth;
    const yRatio = currentScreenHeight / standardScreenHeight;

    // Normalização das coordenadas de clique
    return {
        x: x / xRatio,
        y: y / yRatio
    };
}


/**
 * Função para capturar dados do usuário.
 * 
 * Verifica se o dispositivo usado é mobile (Android, iOS, etc)
 * caso seja, a função é interrompida.
 * 
 * Cria um canvas com largura e altura do tamanho da tela do usuário e o adiciona ao documento.
 * Utiliza a biblioteca html2canvas para obter o conteúdo da página e armazená-lo no canvas.
 * 
 * Armazena as coordenadas X e Y do mouse e o momento em que o evento ocorreu ao mover o mouse.
 * Armazena as coordenadas X e Y do clique e o momento em que o clique ocorreu.
 * 
 * Armazena essas informações no localStorage do navegador.
 * 
 * Define um intervalo para enviar essas informações a uma API a cada 20 segundos.
 * Após o envio, as informações são removidas do localStorage.
 */
async function captureDados() {
    // Verifica se o dispositivo usado é mobile
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        return;
    }

    // Cria um canvas com largura e altura da tela do usuário
    const canvas = document.createElement("canvas");
    canvas.id = "capture";
    canvas.width = document.body.scrollWidth;
    canvas.height = document.body.scrollHeight;

    // Obtém o conteúdo da página e armazena no canvas
    await html2canvas(document.body, {
        canvas,
        onclone(cloneDoc) {
            // Verifica se o canvas já existe no documento clonado
            if (!cloneDoc.getElementById("capture")) {
                // Cria um novo canvas caso não exista
                const canvas = cloneDoc.createElement("canvas");
                canvas.id = "capture";
                canvas.width = document.body.scrollWidth;
                canvas.height = document.body.scrollHeight;
                cloneDoc.body.appendChild(canvas);
            }
            // Oculta o canvas clonado
            cloneDoc.getElementById("capture").style.display = "none";
        },
    });

    // Converte o canvas em uma imagem e armazena a URL da imagem
    const imageData = canvas.toDataURL();
    // Envia a URL da imagem para uma API
    await postData({ imageData: imageData, url: window.location.href, type: "image" });

    // Armazena as coordenadas X e Y do clique ao clicar com o mouse
    document.addEventListener("click", async(event) => {
        // iguala as coordenadas do clique com as coordenadas da tela
        const equalizedClickCoordinates = equalizeClickCoordinates(event.clientX, event.clientY);
        // objeto que armazena as informações do clique, como suas coordenadas, a data/hora e a localização
        const click = {
            x: equalizedClickCoordinates.x,
            y: equalizedClickCoordinates.y,
            dateTime: formatDateTime(new Date()),
            location: await getCurrentLocation(),
        };

        // salva as informações do clique no localStorage como uma string serializada em formato JSON
        localStorage.setItem('clicks', JSON.stringify([...JSON.parse(localStorage.getItem('clicks') || '[]'), click]));
    });

    // Armazena as coordenadas X e Y da movimentação do mouse
    let mouseMovements = [];
    document.addEventListener("mousemove", async(event) => {
        // iguala as coordenadas da movimentação com as coordenadas da tela
        const equalizedMoveCoordinates = equalizeClickCoordinates(event.clientX, event.clientY);
        // objeto que armazena as informações da movimentação do mouse, como suas coordenadas, a data/hora e a localização
        const mouseMovement = {
            x: equalizedMoveCoordinates.x,
            y: equalizedMoveCoordinates.y,
            dateTime: formatDateTime(new Date()),
            location: await getCurrentLocation(),
        };

        // adiciona a movimentação do mouse ao array mouseMovements
        mouseMovements.push(mouseMovement);
        // salva as informações da movimentação do mouse no localStorage como uma string serializada em formato JSON
        localStorage.setItem('movements', JSON.stringify([...JSON.parse(localStorage.getItem('movements') || '[]'), mouseMovement]));
    });

    // Verifica se há intervalos de postagem de dados e, se houver, os limpa
    let postDataInterval;
    if (postDataInterval) {
        clearInterval(postDataInterval);
    }
    // Configura um intervalo para postar os dados armazenados no localStorage a cada 20 segundos
    postDataInterval = setInterval(async() => {
        // verifica se existem dados armazenados de cliques e movimentos do mouse
        if (!localStorage.getItem('clicks') && !localStorage.getItem('movements')) {
            return;
        }

        // posta os dados armazenados (cliques e movimentos do mouse) para o servidor
        await postData({
            clicks: JSON.parse(localStorage.getItem('clicks')),
            movements: JSON.parse(localStorage.getItem('movements')),
            url: window.location.href,
            type: "data"
        });

        // remove os itens postados do localStorage
        localStorage.removeItem('clicks');
        localStorage.removeItem('movements');
    }, 20000);

}

// Essa função faz uma requisição POST com um corpo em formato JSON a uma URL específica.
async function postData(data) {
    // Envia a requisição POST e aguarda a resposta.
    const response = await fetch("DataReceiver.php", {
        method: "POST",
        headers: {
            // Define o tipo de conteúdo da requisição como JSON.
            "Content-Type": "application/json",
        },
        // Serializa os dados fornecidos em um corpo de requisição JSON.
        body: JSON.stringify(data),
    });
    // Retorna a resposta da requisição em formato JSON.
    return response.json();
}


// Essa função formata uma data em uma string no formato "YYYY-MM-DD HH:MM:SS".
function formatDateTime(date) {
    // Retorna a data formatada como uma string.
    return `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`;
}


// Essa função retorna a localização atual do usuário como uma string no formato "latitude, longitude".
async function getCurrentLocation() {
    // Retorna uma promessa que será resolvida com a localização atual do usuário.
    return new Promise((resolve) => {
        // Usa a API de geolocalização do navegador para obter a localização atual do usuário.
        navigator.geolocation.getCurrentPosition((position) => {
            // Resolve a promessa com a localização atual do usuário.
            resolve(`${position.coords.latitude}, ${position.coords.longitude}`);
        }, () => {
            // Se o usuário não permitir o compartilhamento de sua localização, a promessa é resolvida com um valor nulo.
            resolve(null);
        });
    });
}