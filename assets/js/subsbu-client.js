const SubsbuClient = {
    subscribe: (userId) => {
        const form = document.getElementById('subsbuForm');

        const input = document.createElement('input');
        input.setAttribute('type', 'hidden');
        input.setAttribute('value', userId);

        form.appendChild(input);

        form.submit();
    }
};
