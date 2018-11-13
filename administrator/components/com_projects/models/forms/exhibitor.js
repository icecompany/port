'use strict';
window.onload = function () {
    var field1 = document.getElementById("jform_title_ru_short");
    field1.addEventListener('focusout', checkExp, false);
    var field2 = document.getElementById("jform_title_ru_full");
    field2.addEventListener('focusout', checkExp, false);
    var field3 = document.getElementById("jform_title_en");
    field3.addEventListener('focusout', checkExp, false);
    var inn = document.getElementById("jform_inn");
    inn.addEventListener('focusout', checkExp, false);
};

function checkExp() {
    var title = '';
    var t1 = document.getElementById("jform_title_ru_short").value;
    var t2 = document.getElementById("jform_title_ru_full").value;
    var t3 = document.getElementById("jform_title_en").value;
    var inn = document.getElementById("jform_inn").value;
    if (t1.length > 0) title = t1;
    if (t2.length > 0) title = t2;
    if (t3.length > 0) title = t3;
    if (title === '') return;
    var url = '/administrator/index.php?option=com_projects&view=exhibitors&filter_search=' + title + '&format=raw';
    if (inn !== undefined) url += '&inn=' + inn;

    fetch(url)
        .then(function (response) {
            return response.json();
        })
        .then(function (text) {
            var div = document.querySelector('#similar');
            if (text.data.length > 0)
            {
                similarClear();
                div.style.display = 'block';
                text.data.forEach(similar);
            }
            else
            {
                div.style.display = 'none';
            }
        })
        .catch(function (error) {
            console.log('Request failed', error);
        });
}

function similarClear()
{
    var ul = document.querySelector('#similar > ul');
    while (ul.firstChild)
    {
        ul.removeChild(ul.firstChild);
    }
}

function similar(element, index, arr)
{
    var link = document.createElement("a");
    link.href = '/administrator/index.php?option=com_projects&view=exhibitor&layout=edit&id=' + element.id;
    link.target = '_blank';
    link.innerText = element.title;
    var ul = document.querySelector('#similar > ul');
    var li = document.createElement("li");
    li.appendChild(link);
    ul.appendChild(li);
}