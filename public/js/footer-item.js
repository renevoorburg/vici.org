document.addEventListener('DOMContentLoaded', function () {
    const tabList = document.querySelectorAll('#langSelBox li');
    const articles = document.querySelectorAll('article[id^="txt_"]');

    function select(el) {
        el.classList.add('selected');
        el.classList.remove('disabled');
    }

    function disable(el) {
        el.classList.remove('selected');
        el.classList.add('disabled');
    }

    function selectTab(lang) {
        tabList.forEach(tab => {
            if(tab.id === 'xt_' + lang) {
                select(tab);
            } else {
                disable(tab);
            }
        });
        articles.forEach(article => {
            if(article.id === 'txt_' + lang) {
                select(article);
            } else {
                disable(article);
            }
        });
    }

    tabList.forEach(tab => {
        tab.addEventListener('click', function () {
            selectTab(this.id.replace('xt_', ''));
        });
    });
});