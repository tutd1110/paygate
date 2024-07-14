const banks = document.querySelectorAll('.bank-item');
const banktranfer = document.querySelector('#bank_tranfer');
const popup = document.querySelector('#popup');
const overlay = document.querySelector('#overlay');
buttonClosePopup = document.querySelector('#come_back')
imgClosePopup = document.querySelector('#close_popup');
const openPopup = () => {
    document.getElementById('popup').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}
const hiddenPopup = () => {
    document.getElementById('popup').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}
const openBanks = () =>{
    document.getElementById('box_banks').style.display = 'block';
}
banks.forEach(bank => {
    bank.addEventListener('click', (e) => {
        e.preventDefault();
        openPopup();
        const bankImage = bank.querySelector('img').getAttribute('src');
        const popupImgBank = document.querySelector('#popup_img_bank').querySelector('img');
        popupImgBank.setAttribute('src', bankImage);
    })
})
buttonClosePopup.addEventListener('click',(e) =>{
    hiddenPopup();
})
imgClosePopup.addEventListener('click',(e) =>{
    hiddenPopup();
})
banktranfer.addEventListener('click',(e) => {
    e.preventDefault();
    openBanks();
})
const endpoint = 'https://www.gov.uk/bank-holidays.json';
fetch(endpoint)
     .then((response) => response.json())
     .then((data) => console.log(data));