const patternPwd = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,}$/;
let tablaUso = document.getElementById("profile-use-history-table"),
    tablaPago = document.getElementById("profile-pay-history-table"),
    captchaKey;

adjustHistoryContent();
adjustPaymentContent();
setTabActionEventListeners();
setSortingEventListeners();
generateCaptcha();
attachSubmitEventListener();

function adjustHistoryContent () {
    //ForEach sobre los nodos hijos del primer tbody (el único que hay)
    for(let row of tablaUso.tBodies[0].rows){
        let texto = row.cells[2].innerText;
        texto = texto.replaceAll('\\n', ' \u2192 ');
        texto = texto.replaceAll('[USUARIO]', "[Tú]");
        texto = texto.replaceAll('[ASISTENTE]', "[IA]");
        row.cells[2].innerText = texto;
    }
}

function adjustPaymentContent () {
    //ForEach sobre los nodos hijos del primer tbody (el único que hay)
    for(let row of tablaPago.tBodies[0].rows){
        let texto = row.cells[3].innerText;
        texto += '\u20AC' //€
        row.cells[3].innerText = texto;
    }
}

function setTabActionEventListeners () {
    let articleUso = tablaUso.parentElement,
        articlePago = tablaPago.parentElement,
        tabUsos = document.getElementById("tab-usos"),
        tabPagos = document.getElementById("tab-pagos");

    tabUsos.addEventListener('click', (evt) => {
        articleUso.classList.remove('inactive-table');
        articlePago.classList.add('inactive-table');
        tabUsos.classList.add('active-table-tab');
        tabUsos.classList.remove('inactive-table-tab');
        tabPagos.classList.remove('active-table-tab');
        tabPagos.classList.add('inactive-table-tab');
    });

    tabPagos.addEventListener('click', (evt) => {
        articleUso.classList.add('inactive-table');
        articlePago.classList.remove('inactive-table');
        tabPagos.classList.add('active-table-tab');
        tabPagos.classList.remove('inactive-table-tab');
        tabUsos.classList.remove('active-table-tab');
        tabUsos.classList.add('inactive-table-tab');
    });
}

function setSortingEventListeners () {
    let usosTHs = tablaUso.tHead.rows[0].cells,
        pagosTHs = tablaPago.tHead.rows[0].cells;

    //EventListeners para ordenar la tabla de usos al pinchar sobre uno de los th
    // solo si la primera fila no ocupa 4 espacios (está vacía)
    if(tablaUso.tBodies[0].rows[0].cells[0].colSpan !== 4)
        for(let i = 0; i < usosTHs.length; i++) {
            usosTHs[i].addEventListener('click', (evt) => {
                let colNum = Array.from(usosTHs).indexOf(evt.target);
                sortRows(tablaUso, colNum);
            })
        }

    //EventListeners para ordenar la tabla de pagos al pinchar sobre uno de los th
    // solo si la primera fila no ocupa 5 espacios (está vacía)
    if(tablaPago.tBodies[0].rows[0].cells[0].colSpan !== 5)
        for (let i = 0; i < pagosTHs.length; i++){
            pagosTHs[i].addEventListener('click', (evt) => {
                let colNum = Array.from(pagosTHs).indexOf(evt.target);
                sortRows(tablaPago, colNum);
            })
        }
}

function sortRows (tabla, colNum) {
    let rows = Array.from(tabla.tBodies[0].rows),
        field = tabla.tHead.rows[0].cells[colNum].innerText;

    console.log(field);

    //Se ordenan las filas de la columna seleccionada
    // con distintas funciones dependiendo del tipo
    rows.sort((a, b) => {
        switch (field) {
            case "Fecha":
                return dateSort(
                    a.cells[colNum].innerText,
                    b.cells[colNum].innerText
                );
            case "Importe":
                return numSort(
                    a.cells[colNum].innerText.substring(0, a.cells[colNum].innerText.length - 1),
                    b.cells[colNum].innerText.substring(0, b.cells[colNum].innerText.length - 1)
                );
            case "Créditos":
                return numSort(
                    a.cells[colNum].innerText,
                    b.cells[colNum].innerText
                );
            default:
                return alphaSort(
                    a.cells[colNum].innerText,
                    b.cells[colNum].innerText
                );
        }
    });

    //Eliminar las filas viejas
    tabla.tBodies[0].innerHTML = '';

    //Volver a añadirlas, ya ordenadas
    for(let i= 0; i<rows.length; i++) {
        tabla.tBodies[0].append(rows[i]);
    }
}

function alphaSort (a, b) {
    return a.localeCompare(b);
}

function numSort (a, b) {
    console.log(a + ' ' + b)
    a = parseFloat(a);
    b = parseFloat(b);


    switch (true) {
        case (a > b):
            return 1;
        case (a < b):
            return -1;
        default:
            return 0;
    }
}

function dateSort (a, b) {
    a = parseDate(a);
    b = parseDate(b);

    switch (true) {
        case (a.valueOf() > b.valueOf()):
            return 1;
        case (a.valueOf() < b.valueOf()):
            return -1;
        default:
            return 0;
    }
}

function parseDate (str) {
    let parts = str.split(' ');
    parts = [...parts[0].split('/'), ...parts[1].split(':')];
    return new Date(
        parts[2],
        parts[1],
        parts[0],
        parts[3],
        parts[4],
    );
}

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