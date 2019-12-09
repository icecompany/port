var url = "/index.php?option=com_projects&task=api.getCities&api_key=4n98tpw49vtpw496npyww9p6by";
var url_exhibitors = "/index.php?option=com_projects&task=api.getExhibitors&api_key=4n98tpw49vtpw496npyww9p6by";
window.onload = function () {
    let select_elem = jQuery("#jform_regID");
    let select_elem_fact = jQuery("#jform_regID_fact");
    let parent_company = jQuery("#jform_parentID");
    select_elem.chosen();
    select_elem_fact.chosen();
    parent_company.chosen();
    let f = document.querySelector("#jform_regID_chzn .chzn-drop .chzn-search input");
    let ff = document.querySelector("#jform_regID_fact_chzn .chzn-drop .chzn-search input");
    let p = document.querySelector("#jform_parentID_chzn .chzn-drop .chzn-search input");
    unlockSearchCity(f);
    unlockSearchCity(ff);
    unlockSearchCity(p);
    loadCity();
    jQuery(f).autocomplete({source: function () {
            let val = f.value;
            if (val.length < 2) return;
            jQuery.getJSON(`${url}&q=${val}`, function (json) {
                select_elem.empty();
                jQuery.each(json, function (idx, obj) {
                    select_elem.append(`<option value="${obj.id}">${obj.name} (${obj.region})</option>`);
                });
                select_elem.chosen({width: "95%"});
                select_elem.trigger("liszt:updated");
                unlockSearchCity(f);
                f.value = val;
            });
        }
    });
    jQuery(ff).autocomplete({source: function () {
            let val = ff.value;
            if (val.length < 2) return;
            jQuery.getJSON(`${url}&q=${val}`, function (json) {
                select_elem_fact.empty();
                jQuery.each(json, function (idx, obj) {
                    select_elem_fact.append(`<option value="${obj.id}">${obj.name} (${obj.region})</option>`);
                });
                select_elem_fact.chosen({width: "95%"});
                select_elem_fact.trigger("liszt:updated");
                unlockSearchCity(ff);
                ff.value = val;
            });
        }
    });
    jQuery(p).autocomplete({source: function () {
            let val = p.value;
            if (val.length < 2) return;
            jQuery.getJSON(`${url_exhibitors}&q=${val}`, function (json) {
                parent_company.empty();
                jQuery.each(json, function (idx, obj) {
                    parent_company.append(`<option value="${obj.id}">${obj.exhibitor} (${obj.city})</option>`);
                });
                parent_company.chosen({width: "95%"});
                parent_company.trigger("liszt:updated");
                unlockSearchCity(p);
                p.value = val;
            });
        }
    });
};

function unlockSearchCity(f) {
    let chzn = document.querySelector("#jform_regID_chzn");
    chzn.classList.remove("chzn-container-single-nosearch");
    let chzn_fact = document.querySelector("#jform_regID_fact_chzn");
    chzn_fact.classList.remove("chzn-container-single-nosearch");
    let p = document.querySelector("#jform_parentID_chzn");
    p.classList.remove("chzn-container-single-nosearch");
    f.removeAttribute('readonly');
}

function loadCity() {
    let id = document.querySelector("#jform_hidden_city_id").value;
    let title = document.querySelector("#jform_hidden_city_title").value;
    let id_fact = document.querySelector("#jform_hidden_city_fact_id").value;
    let title_fact = document.querySelector("#jform_hidden_city_fact_title").value;
    let id_parent = document.querySelector("#jform_hidden_parent_id").value;
    let title_parent = document.querySelector("#jform_hidden_parent_title").value;
    if (id !== undefined && title !== undefined) {
        let f = document.querySelector("#jform_regID_chzn .chzn-drop .chzn-search input");
        let ff = document.querySelector("#jform_regID_fact_chzn .chzn-drop .chzn-search input");
        let p = document.querySelector("#jform_parentID_chzn .chzn-drop .chzn-search input");
        let select_elem = jQuery("#jform_regID");
        let select_elem_fact = jQuery("#jform_regID_fact");
        let parent_elem = jQuery("#jform_parentID");
        select_elem.append(`<option value="${id}">${title}</option>`);
        select_elem.chosen({width: "95%"});
        select_elem.trigger("liszt:updated");
        select_elem_fact.append(`<option value="${id_fact}">${title_fact}</option>`);
        select_elem_fact.chosen({width: "95%"});
        select_elem_fact.trigger("liszt:updated");
        parent_elem.append(`<option value="${id_parent}">${title_parent}</option>`);
        parent_elem.chosen({width: "95%"});
        parent_elem.trigger("liszt:updated");
        unlockSearchCity(f);
        unlockSearchCity(ff);
        unlockSearchCity(p);
    }
}