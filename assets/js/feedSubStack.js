// Referência: https://mmacfadden.substack.com/p/how-to-display-your-substack-feed

const feedUrl = `https://api.rss2json.com/v1/api.json?rss_url=https%3A%2F%2Fcamecc.substack.com%2Ffeed`;

async function fetchLatestPosts() {
  try {
    const response = await fetch(feedUrl);
    const data = await response.json();

    // Get up to three items from the feed
    const latestItems = (data.items || []).slice(0, 4);

    if (latestItems.length > 0) {
      let postsHTML = latestItems.map(item => {
        // Assuming the first image in the content is the featured image
        const imageUrlMatch = item.content.match(/<img src="([^"]+)"/);
        const imageUrl = imageUrlMatch ? imageUrlMatch[1] : ''; // Default to empty if no image found

        // Extract title
        const title = item.title;

        return `<article class="news">
                        <a href="${item.link}" target="_blank">
                            ${imageUrl ? `<img src="${imageUrl}" alt="${title}" class="cover">` : ''}
                            <p>${title}</p>
                        </a>    
                    </article>`;
      }).join('');

      postsHTML += `<a href="blog/" class="button">+ publicações</a>
                    <a href="blog/quero-publicar.php" class="button">quero publicar</a>`;

      // Update HTML with the latest posts details
      document.getElementById('news-grid').innerHTML = postsHTML;
    } else {
      document.getElementById('latest-posts').textContent = 'Sem publicações no momento.';
    }
  } catch (error) {
    document.getElementById('latest-posts').textContent = 'O site falhou em carregar as publicações recentes.';
    console.error('Error fetching the RSS feed:', error);
  }
}

fetchLatestPosts();