const patternNombre = /^[a-zñáéíóú]+(\s[a-zñáéíóú]+)?$/i;
const patternApellidos = /^[a-zñáéíóú]+(\s[a-zñáéíóú]+)*$/i;
const patternMail = /^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,}$/;
const patternPwd = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,}$/;
const password = document.getElementById("signup-pswd");
const confirm = document.getElementById("signup-confirm");

//Si la validación falla no recarga
document.getElementById('signup-form')
    .addEventListener('submit', (evt) => {
        if (!validate()) {
            //Que hacer si el formulario contiene datos inválidos
            evt.preventDefault();
        }
    });

document.querySelectorAll('input[type=text], input[type=email], input[type=password]')
    .forEach((input) => {
        input.addEventListener('focus', (evt) => {
            document.getElementById(evt.target.id + '-error')
                .innerText = '';
        });
    })
;

function validate() {
    //Comprobar si los datos son validos en el cliente

    if (password.value !== confirm.value) {
        document.getElementById("signup-confirm-error")
            .innerText = "Las contraseñas suministradas no coinciden.";
        return false;
    }

    return true;
}

const campos = [
    { input: document.getElementById("signup-name"),pattern: patternNombre},
    { input: document.getElementById("signup-surname"),pattern: patternApellidos},
    { input: document.getElementById("signup-email"),pattern: patternMail},
    //{ input: document.getElementById("signup-pswd"),pattern: patternPwd},
];

// Función genérica de validación
function validarCampo(input, pattern) {
    const valor = input.value.trim();
    if (valor === "") {
        input.style.backgroundColor = "";
    } else if (pattern.test(valor)) {
        input.style.backgroundColor = "lightgreen";
    } else {
        input.style.backgroundColor = "lightcoral";
    }
}

// Aplicar eventos a todos los campos
campos.forEach(({ input, pattern }) => {
    validarCampo(input, pattern);

    input.addEventListener("input", () => validarCampo(input, pattern));
    input.addEventListener("focus", () => validarCampo(input, pattern));

    input.addEventListener("blur", () => {
        input.style.backgroundColor = "";
    });
});

confirm.addEventListener("input", (e) => {
    if (confirm.value===password.value) {
        e.target.style.backgroundColor = "lightgreen";
    } else {
        e.target.style.backgroundColor = "lightcoral";
    }
});

