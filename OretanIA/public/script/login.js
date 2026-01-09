document.querySelectorAll('input[type=text], input[type=email], input[type=password]')
    .forEach((input) => {
        input.addEventListener('focus', (evt) => {
            document.getElementById(evt.target.id + '-error')
                .innerText = '';
        });
    })
;