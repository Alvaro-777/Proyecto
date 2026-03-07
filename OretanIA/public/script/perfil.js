let tablaUso = document.getElementById("profile-use-history-table");
let tablaPago = document.getElementById("profile-pay-history-table");

adjustHistoryContent();
adjustPaymentContent();
setTabActionEventListeners();
setSortingEventListeners();


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
    let rows = Array.from(tabla.tBodies[0].rows);

    //Se ordenan las filas por orden alfabético del contenido de texto de la columna seleccionada
    rows.sort((a, b) => {
        return a.cells[colNum].innerText.localeCompare(
            b.cells[colNum].innerText
        );
    });

    //Eliminar las filas viejas
    tabla.tBodies[0].innerHTML = '';

    //Volver a añadirlas, ya ordenadas
    for(let i= 0; i<rows.length; i++) {
        console.log(rows[i].cells[3].innerText);
        tabla.tBodies[0].append(rows[i]);
    }
}