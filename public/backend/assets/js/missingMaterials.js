// Subir archivos excel
document
    .getElementById("excelUploadForm")
    .addEventListener("submit", async function (e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const responseMsg = document.getElementById("responseMsg");
        const btn_close_modal = document.querySelector(".btn-close-modal");
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

                // Mostrar modal
                const modalEl = document.getElementById("extraDataModal");
                if (modalEl.parentElement !== document.body) {
                    document.body.appendChild(modalEl);
                }
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
                btn_close_modal.addEventListener("click", () => {
                    modal.hide();
                });
            } else {
                responseMsg.innerHTML = `<div class="alert-validation error">${data.message}</div>`;
            }

            // Desaparecer mensaje
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
            loader.style.display = "none";
        }
    });

// Autocompletar ciudad cuando se selecciona bodega
document.getElementById("almacenId")?.addEventListener("change", () => {
    const almacenSelect = document.getElementById("almacenId");
    const ciudadInput = document.getElementById("ciudad");
    const selectedOption = almacenSelect.options[almacenSelect.selectedIndex];
    ciudadInput.value = selectedOption.getAttribute("data-ciudad") || "";
});

// Click en el botón del modal
document
    .getElementById("btn-send-bodega-id")
    .addEventListener("click", async function (e) {
        e.preventDefault();
        const loader = document.querySelector(".box-loader");
        const modalEl = document.getElementById("extraDataModal");
        const extraForm = document.getElementById("extraDataForm");
        const almacenSelect = document.getElementById("almacenId");
        const ciudadInput = document.getElementById("ciudad").value;

        loader.style.display = "flex";

        try {
            const almacenId = almacenSelect.value;

            const response = await fetch(extraForm.dataset.url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                    Accept: "application/json",
                },
                body: new URLSearchParams({
                    almacen_id: almacenId,
                    ciudad: ciudadInput,
                }),
            });

            const data = await response.json();

            if (response.ok) {
                // Lanzar descarga
                window.location.href = data.file;
            } else {
                alert("Error: " + data.message);
            }
        } catch (err) {
            console.error(err);
            alert("Error inesperado");
        } finally {
            loader.style.display = "none";

            modalEl.classList.remove("show");
            modalEl.classList.add("fade-out");

            // Después de la transición, ocultarlo del todo
            setTimeout(() => {
                responseMsg.innerHTML = `<div class="alert-validation success">Exportado con éxito</div>`;
                modalEl.style.display = "none";
                modalEl.classList.remove("fade-out");
                document.body.classList.remove("modal-open");
                document
                    .querySelectorAll(".modal-backdrop")
                    .forEach((el) => el.remove());

                // Resetear campos
                almacenSelect.value = "";
                document.getElementById("ciudad").value = "";
                setTimeout(() => {
                    responseMsg.innerHTML = "";
                }, 3000);
            }, 200);
        }
    });
