generateCaptcha();
attachSubmitEventListener();

function generateCaptcha () {
    let captcha = document.getElementById('captcha'),
        seed = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM,.-Çç ",
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
            let result = validatePasswordChange();

            //Si es null, la validación no encontró problemas
            if(result){
                evt.preventDefault();

            }
        })
}

function validatePasswordChange () {
    let form = document.forms.namedItem('new-password-form'),
        oldPswd = form.elements.namedItem('old-password').value,
        newPswd = form.elements.namedItem('new-password').value,
        confirmPswd = form.elements.namedItem('confirm-password').value;

    if (newPswd !== confirmPswd)
        return [0, "Las contraseñas nuevas co coinciden"];
    if (patternPwd.test(newPswd))
        return [1, "La nueva contraseña no es valida"];
    if (patternPwd.test(oldPswd))
        return [2, "La contraseña actual es incorrecta"];

    return null;
}