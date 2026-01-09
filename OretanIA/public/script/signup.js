
//Si la validación falla no recarga
document.getElementById('signup-form')
        .addEventListener('submit', (evt) => {
    if(!validate()) {
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
    let nombre = document.getElementById('signup-name').value;
    let apellidos = document.getElementById('signup-surname').value;
    let password = document.getElementById('signup-pswd').value;
    let confirm = document.getElementById('signup-confirm').value;

    if(password !== confirm){
        document.getElementById("signup-confirm-error")
                .innerText = "Las contraseñas suministradas no coinciden.";
        return false;
    }

    return true;
}
