
//Si la validación falla no recarga
document.getElementById('signup-form')
        .addEventListener('submit', (evt) => {
    if(!validate()) {
        //Que hacer si el formulario contiene datos inválidos
        evt.preventDefault();
        alert('Los datos suministrados no son validos')
    }
});


function validate() {
    //Comprobar si los datos son validos en el cliente
    let nombre = document.getElementById('signup-fullname').value;
    let password = document.getElementById('signup-pswd').value;
    let confirm = document.getElementById('signup-confirm').value;

    if(password !== confirm || !nombre.includes(','))
        return false;

    return true;
}
