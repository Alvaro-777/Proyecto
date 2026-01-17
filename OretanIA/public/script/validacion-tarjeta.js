// --- Validación de fecha de expiración ---
document.addEventListener("DOMContentLoaded", () => {
    const fechaInput = document.getElementById("fecha");
    const form = fechaInput?.closest("form");

    if (!fechaInput || !form) return;

    fechaInput.addEventListener("input", () => {
        let valor = fechaInput.value.replace(/\D/g, "");
        if (valor.length > 4) valor = valor.slice(0, 4);
        if (valor.length >= 3) valor = valor.slice(0, 2) + "/" + valor.slice(2);
        fechaInput.value = valor;
    });

    const validarFecha = () => {
        const valor = fechaInput.value.trim();
        if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(valor)) {
            fechaInput.setCustomValidity("Formato inválido (MM/AA)");
            return false;
        }

        const [mes, anio] = valor.split("/").map(Number);
        const hoy = new Date();
        const mesActual = hoy.getMonth() + 1;
        const anioActual = hoy.getFullYear() % 100;

        const esValida = anio > anioActual || (anio === anioActual && mes >= mesActual);
        fechaInput.setCustomValidity(esValida ? "" : "La tarjeta está caducada");
        return esValida;
    };

    fechaInput.addEventListener("blur", validarFecha);
    form.addEventListener("submit", (e) => {
        if (!validarFecha()) {
            e.preventDefault();
            fechaInput.reportValidity();
        }
    });
});

// --- Formateo y validación de tarjeta ---
document.addEventListener("DOMContentLoaded", () => {
    const tarjetaInput = document.getElementById("tarjeta");
    const form = tarjetaInput?.closest("form");

    if (!tarjetaInput || !form) return;

    tarjetaInput.addEventListener("input", function () {
        let valor = this.value.replace(/\D/g, "");
        if (valor.length > 16) valor = valor.slice(0, 16);
        let grupos = [];
        for (let i = 0; i < valor.length; i += 4) {
            grupos.push(valor.slice(i, i + 4));
        }
        this.value = grupos.join(" ");
    });

    form.addEventListener("submit", function (e) {
        const valorLimpio = tarjetaInput.value.replace(/\s/g, "");
        if (valorLimpio.length !== 16) {
            tarjetaInput.setCustomValidity("Debe tener exactamente 16 dígitos");
            tarjetaInput.reportValidity();
            e.preventDefault();
        } else {
            tarjetaInput.setCustomValidity("");
        }
    });
});