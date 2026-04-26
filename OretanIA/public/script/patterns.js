export const patternNombre = /^[a-zñáéíóú]+(\s[a-zñáéíóú]+)?$/i;
export const patternApellidos = /^[a-zñáéíóú]+(\s[a-zñáéíóú]+)*$/i;
export const patternMail = /^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,}$/;
export const patternPswd = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,}$/;