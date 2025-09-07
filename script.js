document.addEventListener('DOMContentLoaded', function() {

    // Função para controlar a visibilidade dos dropdowns
    function setupDropdown(buttonClass, dropdownClass) {
        const button = document.querySelector(buttonClass);
        const dropdown = document.querySelector(dropdownClass);

        if (button && dropdown) {
            button.addEventListener('mouseenter', function() {
                dropdown.classList.add('show');
            });

            button.addEventListener('mouseleave', function() {
                dropdown.classList.remove('show');
            });
        }
    }
    

    // Configura cada botão com seu respectivo dropdown
    setupDropdown('.CadButton', '.dropdown-content1');
    setupDropdown('.VisButton', '.dropdown-content2');
    setupDropdown('.ExButton', '.dropdown-content3');

});