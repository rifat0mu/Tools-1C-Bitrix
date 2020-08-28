/**
 * Как получить текущие имя при нажатии на кнопку?
 */
document.querySelectorAll('.css').forEach(item => {
    item.addEventListener('click', e => {
        console.log(e.target.innerText);
    })
});