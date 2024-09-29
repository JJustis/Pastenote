document.addEventListener('DOMContentLoaded', () => {
    const pasteForm = document.getElementById('pasteForm');
    const pasteResult = document.getElementById('paste-result');
    const pasteUrl = document.getElementById('pasteUrl');
    const pasteContent = document.getElementById('pasteContent');
    const darkModeToggle = document.getElementById('darkModeToggle');
    const isLockedCheckbox = document.getElementById('isLocked');
    const paymentGoalGroup = document.getElementById('paymentGoalGroup');

    isLockedCheckbox.addEventListener('change', () => {
        paymentGoalGroup.style.display = isLockedCheckbox.checked ? 'block' : 'none';
    });

    pasteForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const title = document.getElementById('pasteTitle').value;
        const content = document.getElementById('pasteContent').value;
        const syntax = document.getElementById('pasteSyntax').value;
        const expiration = document.getElementById('pasteExpiration').value;
        const tags = document.getElementById('pasteTags').value;
        const isLocked = document.getElementById('isLocked').checked;
        const paymentGoal = document.getElementById('paymentGoal').value;

        try {
            const response = await fetch('create_paste.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `title=${encodeURIComponent(title)}&content=${encodeURIComponent(content)}&syntax=${syntax}&expiration=${expiration}&tags=${encodeURIComponent(tags)}&is_locked=${isLocked}&payment_goal=${paymentGoal}`,
            });

            if (response.ok) {
                const data = await response.json();
                pasteUrl.href = `http://betahut.bounceme.net/pastenote/view_paste.php?id=${data.id}`;
                pasteUrl.textContent = pasteUrl.href;
                pasteContent.textContent = content;
                pasteContent.className = `language-${syntax}`;
                hljs.highlightElement(pasteContent);
                pasteResult.style.display = 'block';
                pasteForm.reset();
            } else {
                throw new Error('Failed to create paste');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while creating the paste. Please try again.');
        }
    });

    darkModeToggle.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const icon = darkModeToggle.querySelector('i');
        icon.classList.toggle('fa-moon');
        icon.classList.toggle('fa-sun');
    });
	document.getElementById('pasteForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);

    fetch('create_paste.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.id) {
            // Display the paste URL and content
            const pasteUrl = `${window.location.origin}/view_paste.php?id=${data.id}`;
            document.getElementById('pasteUrl').href = pasteUrl;
            document.getElementById('pasteUrl').textContent = pasteUrl;
            
            // You may want to fetch and display the paste content here
            // For demonstration, we assume you fetch it and set it
            document.getElementById('pasteContentDisplay').textContent = formData.get('content');
            
            // Show the result section
            document.getElementById('paste-result').style.display = 'block';
        } else {
            alert('An error occurred while creating the paste.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the paste.');
    });
});
document.getElementById('darkModeToggle').addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
});

function copyEmbedCode() {
    var embedCodeInput = document.getElementById("embedCode");
    embedCodeInput.select();
    embedCodeInput.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Embed code copied to clipboard");
}

});