document.body.style.display = "none";
document.addEventListener("DOMContentLoaded", async () => {
    try {
        const response = await fetch("/fetch.php");
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        await response.json().then((data) => {
            const videoCard = document.querySelector(".video-card");
            let publishedAt = null;
            if (data.isLive) {
                document.getElementById("card-title").textContent = "STREAMING";
                document.getElementById("video-ribbon").style.backgroundColor = "red";
                document.getElementById("video-status").textContent = "LIVE";
                document.getElementById("thumbnail").src = data.liveStream.thumbnail;
                document.getElementById("video-title").textContent = data.liveStream.title;
                document.getElementById("video-title").title = data.liveStream.title;
                document.getElementById("video-link").href = data.liveStream.link;
                document.getElementById("timer-status").textContent = "INTO THE STREAM. WAH!";
                publishedAt = data.liveStream.publishedAt;
            } else if (data.archivedStreams) {
                document.getElementById("card-title").textContent = "LATEST CONTENT";
                document.getElementById("video-ribbon").style.backgroundColor = "gray";
                document.getElementById("video-status").textContent = "OFFLINE";
                document.getElementById("thumbnail").src = data.archivedStreams[0].thumbnail;
                document.getElementById("video-title").textContent = data.archivedStreams[0].title;
                document.getElementById("video-title").title = data.archivedStreams[0].title;
                document.getElementById("video-link").href = data.archivedStreams[0].link;
                document.getElementById("timer-status").textContent = "WITHOUT INA :(";
                publishedAt = data.archivedStreams[0].publishedAt;
            } else {
                videoCard.innerHTML = "<p>No video available</p>";
            }
            document.body.style.display = "flex";
            startTimer(publishedAt, data.isLive);
        });
    } catch (error) {
        console.error("Failed to fetch or update data:", error);
    }
});

function startTimer(time, live) {
    const startTimeString = time;
    const startTime = new Date(startTimeString);
    const timerElement = document.getElementById("timer");

    function updateTimer() {
        const now = new Date();
        let elapsed;

        if (live) {
            elapsed = Math.floor((now - startTime) / 1000);
        } else {
            elapsed = Math.floor((now.getTime() - startTime.getTime()) / 1000);
        }

        const days = Math.floor(elapsed / (24 * 3600));
        const hours = Math.floor((elapsed % (24 * 3600)) / 3600);
        const minutes = Math.floor((elapsed % 3600) / 60);
        const seconds = elapsed % 60;

        timerElement.innerText = `${String(days).padStart(2, '0')}:${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
    setInterval(updateTimer, 1000);
}
