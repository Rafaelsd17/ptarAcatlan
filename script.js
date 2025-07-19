function abrirModal(id) {
    document.getElementById(id).style.display = "block";
}

function cerrarModal() {
    document.getElementById("modal").style.display = "none";
}

window.onclick = function(event) {
    let modal = document.getElementById("modal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}

function agregarEventosTarjetas() {
    const cards = document.querySelectorAll(".card");

    cards.forEach(card => {
        card.addEventListener("click", function (e) {
            e.preventDefault();

            const titulo = this.querySelector("strong").innerText;
            const descripcion = this.querySelector("p").innerText;
            const imagen = this.querySelector("img").getAttribute("src");

            document.getElementById("modal-titulo").innerText = titulo;
            document.getElementById("modal-texto").innerText = descripcion + "\n\nEste es un ejemplo de texto ampliado que podrías extender para dar más detalles específicos sobre la tarjeta seleccionada.";
            document.getElementById("modal-img").setAttribute("src", imagen);

            document.getElementById("modal").style.display = "flex";
        });
    });
}



function cambiarContenido(seccion) {
    // Oculta todas las secciones
    const secciones = document.querySelectorAll('#contenido > div');
    secciones.forEach(sec => {
        sec.style.display = 'none';
    });

    // Muestra la sección deseada
    const mostrar = document.getElementById(`seccion-${seccion}`);
    if (mostrar) {
        mostrar.style.display = 'block';
    }

    // Vuelve a activar las tarjetas si se muestra la sección inicio
    if (seccion === "inicio") {
        agregarEventosTarjetas();
    }

    // Cambia la clase activa del menú
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });

    const linkActivo = document.querySelector(`.nav-link[href="#${seccion}"]`);
    if (linkActivo) {
        linkActivo.classList.add('active');
    }
}

// SLIDER DE CURIOSIDADES
let indiceActual = 0;
let imagenes = [];
let puntos = [];

function mostrarAviso(indice) {
    if (!imagenes.length || !puntos.length) return;

    imagenes.forEach((img, i) => {
        img.style.opacity = i === indice ? "1" : "0";
        puntos[i].classList.toggle("activo", i === indice);
    });
    indiceActual = indice;
}

function cambiarAvisos() {
    imagenes = document.querySelectorAll(".aviso-img");
    puntos = document.querySelectorAll(".punto");

    mostrarAviso(indiceActual);

    setInterval(() => {
        indiceActual = (indiceActual + 1) % imagenes.length;
        mostrarAviso(indiceActual);
    }, 3000);
}

// INICIAR TODO AL CARGAR
document.addEventListener("DOMContentLoaded", () => {
    agregarEventosTarjetas();
    cambiarAvisos();
    cambiarContenido("inicio"); // Muestra la sección 'inicio' por defecto

    // Evento login
    document.getElementById("loginForm").addEventListener("submit", function(event) {
        event.preventDefault();
        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;

        const validUsername = "admin";
        const validPassword = "123456";

        if (username === validUsername && password === validPassword) {
            document.getElementById("loginContainer").style.display = "none";
            document.getElementById("contenido-protegido").style.display = "block";
        } else {
            document.getElementById("error").style.display = "block";
        }
    });

    // Evento de navegación
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const hash = this.getAttribute('href').substring(1);
            cambiarContenido(hash);
        });
    });
});
