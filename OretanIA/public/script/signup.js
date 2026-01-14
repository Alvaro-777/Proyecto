const nombre = document.getElementById('signup-name');
const apellidos = document.getElementById('signup-surname');
const mail = document.getElementById('signup-email');
const password = document.getElementById('signup-pswd');
const confirm = document.getElementById('signup-confirm');
const patternNombre = /^[a-zñáéíóú]+(\s[a-zñáéíóú]+)?$/i;
const patternApellidos = /^[a-zñáéíóú]+(\s[a-zñáéíóú]+)*$/i;
const patternMail = /^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,}$/;
const patternPwd = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,}$/;
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

nombre.addEventListener("input", (e) => {
    const valor = e.target.value;
    if (patternNombre.test(valor)) {
        e.target.style.backgroundColor = "lightgreen";
    } else {
        e.target.style.backgroundColor = "lightcoral";
    }
});

apellidos.addEventListener("input", (e) => {
    const valor = e.target.value;
    if (patternApellidos.test(valor)) {
        e.target.style.backgroundColor = "lightgreen";
    } else {
        e.target.style.backgroundColor = "lightcoral";
    }
});
mail.addEventListener("input", (e) => {
    const valor = e.target.value;
    if (patternMail.test(valor)) {
        e.target.style.backgroundColor = "lightgreen";
    } else {
        e.target.style.backgroundColor = "lightcoral";
    }
});
/*password.addEventListener("input", (e) => {
    const valor = e.target.value;
    if (patternPwd.test(valor)) {
        e.target.style.backgroundColor = "lightgreen";
    } else {
        e.target.style.backgroundColor = "lightcoral";
    }
});*/

confirm.addEventListener("input", (e) => {
    if (confirm.value===password.value) {
        e.target.style.backgroundColor = "lightgreen";
    } else {
        e.target.style.backgroundColor = "lightcoral";
    }
});

