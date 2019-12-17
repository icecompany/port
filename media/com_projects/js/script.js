'use strict';
function clrFilters() {
    var project = document.querySelector("[name='filter_project']");
    if (project !== null) project.selectedIndex = '';
    var exhibitor = document.querySelector("[name='filter_exhibitor']");
    if (exhibitor !== null) exhibitor.selectedIndex = '';
    var state = document.querySelector("[name='filter_state']");
    if (state !== null) state.selectedIndex = '';
    var contract = document.querySelector("[name='filter_contract']");
    if (contract !== null) contract.selectedIndex = '';
    var status = document.querySelector("[name='filter_status']");
    if (status !== null) status.selectedIndex = '';
    var activity = document.querySelector("[name='filter_activity']");
    if (activity !== null) activity.selectedIndex = '';
    var price = document.querySelector("[name='filter_price']");
    if (price !== null) price.selectedIndex = '';
    var section = document.querySelector("[name='filter_section']");
    if (section !== null) section.selectedIndex = '';
    var manager = document.querySelector("[name='filter_manager']");
    if (manager !== null) manager.selectedIndex = '';
    var dat = document.querySelector("[name='filter_dat']");
    if (dat !== null) dat.value = '';
    var city = document.querySelector("[name='filter_city']");
    if (city !== null) city.value = '';
    var projectinactive = document.querySelector("[name='filter_projectinactive']");
    if (projectinactive !== null) projectinactive.value = '';
    var search = document.querySelector("[name='filter_search']");
    if (search !== null) search.value = '';
}

function imp(to)
{
    var id = document.querySelector("#valimp option:checked");
    console.log(id.value);
    if (id.value !== undefined)
    {
        location.href='/administrator/index.php?option=com_projects&task=sections.import&from=' + id.value + '&to=' + to;
    }
}
function updateTodo(id) {
    var result = document.querySelector("#todo_res_"+id).value;
    if (result.length < 1) return false;
    fetch('/administrator/index.php?option=com_projects&task=todos.close&id=' + id + '&result=' + result)
        .then(function (response) {
            return response.json();
        })
        .then(function (text) {
            var td = document.querySelector('.resultTodo_' + id);
            td.textContent = result;
            /*td = document.querySelector('.resultTodoDat_' + id);
            td.textContent = text.data.dat;
            td = document.querySelector('.resultTodoState_' + id);
            td.textContent = 'Выполнена';*/
        })
        .catch(function (error) {
            console.log('Request failed', error);
        });
}
function showCard(id) {
    fetch('/administrator/index.php?option=com_projects&view=exhibitor&layout=edit&id=' + id + '&format=raw')
        .then(function (response) {
            return response.json();
        })
        .then(function (text) {
            document.querySelector(".modal-body").style.maxHeight = '2048px';
            var title = document.querySelector('#modalExpCardTitle');
            title.textContent = text.data.info.title;
            var myNode = document.querySelector('#cardValues');
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }
            if (text.data.info.director_name != null) {
                var director_name = document.createElement('p');
                director_name.textContent = 'Руководитель: ' + text.data.info.director_name;
                myNode.appendChild(director_name);
            }
            if (text.data.info.director_post != null) {
                var director_post = document.createElement('p');
                director_post.textContent = 'Должность: ' + text.data.info.director_post;
                myNode.appendChild(director_post);
            }
            if (text.data.info.phone_1 != null) {
                var phone1 = document.createElement('p');
                phone1.textContent = 'Телефон 1: ' + text.data.info.phone_1;
                if (text.data.info.phone_1_comment != null) {
                    phone1.textContent += ' (' + text.data.info.phone_1_comment + ')';
                }
                myNode.appendChild(phone1);
            }
            if (text.data.info.phone_2 != null) {
                var phone2 = document.createElement('p');
                phone2.textContent = 'Телефон 2: ' + text.data.info.phone_2;
                if (text.data.info.phone_2_comment != null) {
                    phone2.textContent += ' (' + text.data.info.phone_2_comment + ')';
                }
                myNode.appendChild(phone2);
            }
            if (text.data.info.email != null) {
                var email = document.createElement('p');
                var url = document.createElement('a');
                url.href = 'mailto:' + text.data.info.email;
                url.innerText = text.data.info.email;
                email.appendChild(url);
                myNode.appendChild(document.createTextNode('Электронная почта: '));
                myNode.appendChild(email);
            }
            if (text.data.info.site != null) {
                var site = document.createElement('p');
                var url1 = document.createElement('a');
                url1.href = text.data.info.site;
                url1.innerText = text.data.info.site;
                url1.setAttribute('target', '_blank');
                site.appendChild(url1);
                myNode.appendChild(document.createTextNode('Веб-сайт: '));
                myNode.appendChild(site);
            }
            myNode = document.querySelector('#contactsValues');
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }
            for (var i = 0; i < text.data.persons.length; i++)
            {
                if (text.data.persons[i].main === '1')
                {
                    if (text.data.persons[i].fio != null) {
                        var fio = document.createElement('p');
                        fio.textContent = 'ФИО: ' + text.data.persons[i].fio;
                        myNode.appendChild(fio);
                    }
                    if (text.data.persons[i].post != null) {
                        var post = document.createElement('p');
                        post.textContent = 'Должность: ' + text.data.persons[i].post;
                        myNode.appendChild(post);
                    }
                    if (text.data.persons[i].phone_work != null) {
                        var phone_work = document.createElement('p');
                        phone_work.textContent = 'Рабочий тел.: ' + text.data.persons[i].phone_work;
                        myNode.appendChild(phone_work);
                    }
                    if (text.data.persons[i].phone_mobile != null) {
                        var phone_mobile = document.createElement('p');
                        phone_mobile.textContent = 'Мобильный тел.: ' + text.data.persons[i].phone_mobile;
                        myNode.appendChild(phone_mobile);
                    }
                    if (text.data.persons[i].email_clean != null) {
                        var email = document.createElement('p');
                        var url = document.createElement('a');
                        url.href = 'mailto:' + text.data.persons[i].email_clean;
                        url.innerText = text.data.persons[i].email_clean;
                        email.appendChild(url);
                        myNode.appendChild(document.createTextNode('Электронная почта: '));
                        myNode.appendChild(url);
                    }
                    if (text.data.persons[i].comment != null) {
                        var comment = document.createElement('p');
                        comment.textContent = 'Комментарий: ' + text.data.persons[i].comment;
                        myNode.appendChild(comment);
                    }
                    var hr = document.createElement('hr');
                    hr.style.width = '80%';
                    myNode.appendChild(hr);
                }
            }
        })
        .catch(function (error) {
            console.log('Request failed', error);
        });
}
