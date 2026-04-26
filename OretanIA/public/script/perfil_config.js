import {patternPswd, patternMail, patternNombre, patternApellidos} from "./patterns.js";

let jsErrors = 0, captchaKey = '';

generateCaptcha();
attachSubmitEventListener();

function generateCaptcha () {
    let captcha = document.getElementById('captcha'),
        seed = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890,.-Çç;:+*_()[]{}",
        keyLength = 10;

    captchaKey = '';
    for(let i=0; i<keyLength; i++)
        captchaKey += seed.charAt(
            Math.floor(Math.random() * seed.length)
        );

    captcha.innerText = captchaKey;
}

function attachSubmitEventListener () {
    document.forms.namedItem('new-password-form')
        .addEventListener('submit', (evt) => {
            //Si es null, la validación no encontró problemas
            if(validatePasswordChange()) evt.preventDefault();
        });

    document.querySelector('.delete-button')
        .addEventListener('click', () => {
            validateAccountDeletion()
                .then((validationResult) => {
                    if (validationResult) return displayError(validationResult);

                    let theme =
                        document.querySelector('body').classList.contains('oscuro') ?
                            'dark' : 'light';

                    //Swal es sweetalert2.min.all.js, importado en la plantilla
                    Swal.fire({
                        title: 'Borrar Cuenta',
                        html: '¿Seguro que quieres borrar tu cuenta?<br>Este proceso es irrevertible',
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Cancelar y volver',
                        confirmButtonText: 'Borrar mi cuenta',
                        theme: theme
                    }).then((result) => {
                        if (result.isConfirmed)
                            document.forms
                                .namedItem('delete-account-form')
                                .submit();
                    })
                });

        });
}

function validatePasswordChange () {
    let form = document.forms.namedItem('new-password-form'),
        oldPswd = form.elements.namedItem('old-password').value,
        newPswd = form.elements.namedItem('new-password').value,
        confirmPswd = form.elements.namedItem('confirm-password').value;

    if (newPswd !== confirmPswd)
        return [0, "Las contraseñas nuevas co coinciden"];
    if (patternPswd.test(newPswd))
        return [1, "La nueva contraseña no es valida"];
    if (patternPswd.test(oldPswd))
        return [2, "La contraseña actual es incorrecta"];

    return null;
}

async function validateAccountDeletion () {
    let form = document.forms.namedItem('delete-account-form'),
        email = form.elements.namedItem('email').value,
        pswd = form.elements.namedItem('password').value,
        captcha = form.elements.namedItem('captcha').value;

    switch (true) {
        case (!email):
            return 'El correo es un campo obligatorio';
        case (!patternMail.test(email)):
            return 'El correo no tiene un formato valido';
        case (!pswd):
            return 'La contraseña es un campo obligatorio';
        case (!patternPswd.test(pswd)):
            return 'La contraseña no cumple las restricciones';
        case (!captcha):
            return 'El captcha es un campo obligatorio';
        case (captcha !== captchaKey):
            return 'El captcha no fue resuelto correctamente';
    }
    let credentialsOK = await checkCredentials(email, pswd);

    return credentialsOK ? false : 'El correo o la contraseña no son correctos'
}

async function checkCredentials(email, password) {
    let params = new URLSearchParams();
    params.append('mail', email);
    params.append('pswd', password);

    return fetch('../verify-credentials', {
        method: "POST",
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: params
    })
        .then(res => {
            return res.ok;
        })
        .catch(err => {
            console.error(err);
            return false;
        })
}

function displayError (message) {
    jsErrors++;
    let errorDiv = document.createElement('div');
    errorDiv.innerHTML =
        message +
        "<button class='close-button'" +
        " onclick='document.getElementById(\"js-error-" + jsErrors + "\").remove();'>" +
        "✕" +
        "</button>";
    errorDiv.id = 'js-error-' + jsErrors;
    errorDiv.classList.add('error-notice');

    document.querySelector('#profile-config-error-board')
        .append(errorDiv);

    return false
}