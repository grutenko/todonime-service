/**
 * Вернет true если подсказка с именем name уже была показана
 * @param {String} name 
 * @return {Boolean}
 */
export function alreadyShowed( name ) {
    return getAllShowed().indexOf(name) !== -1;
}

export function setShow( name ) {
    if( !alreadyShowed(name) ) {
        var promt = getAllShowed();
        promt.push(name);
        save( promt );   
    }
}

/**
 * Возвращает массив с именами всех показаных подсказок.
 * @return {Array}
 */
export function getAllShowed() {
    return JSON.parse( localStorage.getItem('showedPromts') || '[]' );
}

/**
 * Сохраняет новый список показаных подсказок.
 * @param {array} promts 
 */
function save( promts ) {
    localStorage.setItem('showedPromts', JSON.stringify(promts));
}