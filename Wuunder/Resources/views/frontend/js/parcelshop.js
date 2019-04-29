// Get the modal
var shippingAddress;
var parcelshopAddress;
var parcelshopShippingMethodElem = document.getElementById('confirm_dispatch18');
var exists = false;
var save = true;




function r(f){/in/.test(document.readyState)?setTimeout('r('+f+')',9):f()}
r(function(){
    initParcelshopLocator();
    $('body > div.page-wrap > section > div > div > div > div > div.table--actions.actions--bottom > div > button').on('click', checkParcelshopSelection);
    $('body > div.page-wrap > section > div > div > div > div > div.confirm--actions.table--actions.block.actions--bottom > button').on('click', checkParcelshopSelection);
    jQuery.subscribe('plugin/swShippingPayment/onInputChanged', initParcelshopLocator);
});

function checkParcelshopSelection(event) {
    selected = $('#checked').value;
    if (!selected) {
        event.preventDefault();
        alert('select a parcelshop');
    }
}

function initParcelshopLocator() {
    if (parcelshopShippingMethodElem) {
        parcelshopShippingMethodElem.onchange = _onShippingMethodChange;
        _onShippingMethodChange();
    }
}

function _checkIfParcelshopExists() {
    exists = true;
    save = false;
    jQuery.post( baseUrl + '/frontend/wuunder_parcelshop/parcelshop_check', function( data ) {
        if(data !== "null") {
            parcelshopId = JSON.parse(data);
            _loadSelectedParcelshopAddress(parcelshopId);
        }
    });
}

function _onShippingMethodChange() {
    let parcelshopShippingMethodElem = document.getElementById('confirm_dispatch18');
    if (parcelshopShippingMethodElem.checked) {
        var container = document.createElement('tr');
        container.className += "chooseParcelshop";
        container.innerHTML = '<td></td><td><div id="parcelshopsSelectedContainer" onclick="_showParcelshopLocator()"><a href="#/" id="selectParcelshop">Klik hier om een parcelshop te kiezen</a></div></td><input type="hidden" id="selected" value="false">';
        // window.parent.document.getElementsByClassName('shipping')[0].appendChild(container);
        window.parent.document.getElementsByClassName(parcelshopShippingMethodElem.parentNode.parentNode.append(container));
        _checkIfParcelshopExists();
        if(!exists) {
            _printParcelshopAddress();
        }
    } else {
        var containerElems = window.parent.document.getElementsByClassName('chooseParcelshop');
        if (containerElems.length) {
            containerElems[0].remove();
        }
    }
}

function _printParcelshopAddress() {

    if (parcelshopAddress) {
        if (window.parent.document.getElementsByClassName("parcelshopInfo").length) {
            window.parent.document.getElementsByClassName("parcelshopInfo")[0].remove();
        }
        var currentParcelshop = document.createElement('div');
        currentParcelshop.className += 'parcelshopInfo';
        currentParcelshop.innerHTML = '<br/><strong>Ophalen in parcelshop:</strong><br/>' + parcelshopAddress;
        window.parent.document.getElementById('parcelshopsSelectedContainer').appendChild(currentParcelshop);
        window.parent.document.getElementById('selectParcelshop').innerHTML = 'klik hier om een andere parcelshop te kiezen';
    }
}


function _showParcelshopLocator() {
    jQuery.post(baseUrl + "/frontend/wuunder_parcelshop/address", function( data ) {
            data = JSON.parse(data);
            shippingAddress = data[0].addressInfo;
            _openIframe(data[0].apiUrl, data[0].availableCarriers);
        });
}


function _openIframe(baseUrlApi, availableCarrierList) {
    var iframeUrl = baseUrlApi + 'parcelshop_locator/iframe/?lang=nl&availableCarriers=' + availableCarrierList + '&address=' + shippingAddress;
    var iframeContainer = document.createElement('div');
    iframeContainer.className = "parcelshopPickerIframeContainer";
    var iframeDiv = document.createElement('div');
    iframeDiv.innerHTML = '<iframe src="' + iframeUrl + '" width="100%" height="100%">';
    iframeDiv.className = "parcelshopPickerIframe";
    iframeDiv.style.cssText = 'position: fixed; top: 0; left: 0; bottom: 0; right: 0; z-index: 2147483647';
    iframeContainer.appendChild(iframeDiv);
    window.parent.document.getElementsByClassName("chooseParcelshop")[0].appendChild(iframeContainer);

    function removeServicePointPicker() {
        removeElement(iframeContainer);
    }

    function onServicePointSelected(messageData) {
        _loadSelectedParcelshopAddress(messageData.parcelshopId);
        removeServicePointPicker();
    }

    function onServicePointClose() {
        removeServicePointPicker();
    }

    function onWindowMessage(event) {
            messageData = event.data;
        var messageHandlers = {
            'servicePointPickerSelected': onServicePointSelected,
            'servicePointPickerClose': onServicePointClose
        };
        if (!(messageData.type in messageHandlers)) {
            alert('Invalid event type');
            return;
        }
        var messageFn = messageHandlers[messageData.type];
        messageFn(messageData);
    }

    window.addEventListener('message', onWindowMessage, false);
}

function _loadSelectedParcelshopAddress(id) {
    jQuery.post( baseUrl + '/frontend/wuunder_parcelshop/parcelshop_info', {parcelshop_id: id, save: save}, function( data ) {
        data = JSON.parse(data);
        var parcelshopInfoHtml = _capFirst(data.company_name) + "<br>" + _capFirst(data.address.street_name) +
            " " + data.address.house_number + "<br>" + data.address.city;
        parcelshopInfoHtml = parcelshopInfoHtml.replace(/"/g, '\\"').replace(/'/g, "\\'");
        parcelshopAddress = parcelshopInfoHtml;
        _printParcelshopAddress();
    });
    document.cookie = "parcelshop_id=" + id;
}

// Capitalizes first letter of every new word.
function _capFirst(str) {
    if (str === undefined)
        return "";
    return str.replace(/\w\S*/g, function (txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}

function removeElement(element) {
    if (element.remove !== undefined) {
        element.remove();
    } else {
        element && element.parentNode && element.parentNode.removeChild(element);
    }
}
