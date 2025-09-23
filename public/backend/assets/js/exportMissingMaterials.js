// Subir archivo excel con loader
document
    .getElementById("excelUploadForm")
    .addEventListener("submit", async function (e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const responseMsg = document.getElementById("responseMsg");
        const loader = document.querySelector(".box-loader");

        loader.style.display = "flex";

        try {
            const response = await fetch(form.dataset.url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await response.json();

            if (response.ok) {
                responseMsg.innerHTML = `<div class="alert-validation success">${data.message}</div>`;
                form.reset();

                if (data.file) {
                    // Lanzar descarga automática del archivo filtrado
                    window.location.href = data.file;
                }
            } else {
                responseMsg.innerHTML = `<div class="alert-validation error">${data.message}</div>`;
            }

            // Quitar mensajes después de 2 segundos
            setTimeout(() => {
                const alert = responseMsg.querySelector(".alert-validation");
                if (alert) {
                    alert.classList.add("fade-out");
                    setTimeout(() => {
                        responseMsg.innerHTML = "";
                    }, 500);
                }
            }, 2000);
        } catch (error) {
            console.error(error);
            responseMsg.innerHTML = `<div class="alert-validation error">Error inesperado</div>`;

            setTimeout(() => {
                const alert = responseMsg.querySelector(".alert-validation");
                if (alert) {
                    alert.classList.add("fade-out");
                    setTimeout(() => {
                        responseMsg.innerHTML = "";
                    }, 500);
                }
            }, 2000);
        } finally {
            loader.style.display = "none"; // Ocultar loader
        }
    });
