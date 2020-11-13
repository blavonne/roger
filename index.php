<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8" />
<title>blavonne 21</title>
<link rel="stylesheet" href="/style.css">
</head>
<body>
	<script>
		var i, j, elements = [];
		var n = 20;
		var map = document.createElement('div');
		var btn = document.createElement('input');
		var head = document.createElement('div');

		map.className = 'map';
		head.className = 'head';
		head.innerHTML = "Game of life";
		btn.setAttribute("type", "button");
		btn.setAttribute("value", "Still alive");
		btn.addEventListener('click', chacha);
		document.body.appendChild(head);
		document.body.appendChild(map);
		document.body.appendChild(btn);
		for (i = 0; i < n; i++)
		{
			elements[i] = [];
			for (j = 0; j < n; j++)
			{
				elements[i][j] = document.createElement('div');
				elements[i][j].className = 'dead';
				map.appendChild(elements[i][j]);
			}
		}
		currentInterval = null;
		for (i = 0; i < n; i++)
		{
			for (j = 0; j < n; j++)
			{
				elements[i][j].addEventListener('click', function(live)
				{
					if (currentInterval !== nulL)
						return ;
					live.target.classList.remove('dead');
					live.target.classList.add('alive');
				}, false);
			}
		}
		function next_gen()
		{
			let friends;

			for (i = 0; i < n; i++)
			{
				for (j = 0; j < n; j++)
				{
					friends = 0;
					if (elements[i][j].classList.contains('alive'))
					{
						if ((i - 1 >= 0) && (j - 1 >= 0))
						{
							if (elements[i - 1][j - 1].classList.contains('alive'))
								friends++;
						}
						if (j - 1 >= 0)
						{
							if (elements[i][j - 1].classList.contains('alive'))
								friends++;
						}
						if ((i + 1 < n) && (j - 1 >= 0))
						{
							if (elements[i + 1][j - 1].classList.contains('alive'))
								friends++;
						}
						if (i - 1 >= 0)
						{
							if (elements[i - 1][j].classList.contains('alive'))
								friends++;
						}
						if ((i - 1 >= 0) && (j + 1 < n))
						{
							if (elements[i - 1][j + 1].classList.contains('alive'))
								friends++;
						}
						if (j + 1 < n)
						{
							if (elements[i][j + 1].classList.contains('alive'))
								friends++;
						}
						if (i + 1 < n)
						{
							if (elements[i + 1][j].classList.contains('alive'))
								friends++;
						}
						if ((i + 1 < n) && (j + 1 < n))
						{
							if (elements[i + 1][j + 1].classList.contains('alive'))
								friends++;
						}
						if ((friends < 2) || (friends > 3))
							elements[i][j].classList.add('can_die');
					}
					else if (elements[i][j].classList.contains('dead'))
					{
						if (i - 1 >= 0 && j - 1 >= 0)
						{
							if (elements[i - 1][j - 1].classList.contains('alive'))
								friends++;
						}
						if (j - 1 >= 0)
						{
							if (elements[i][j - 1].classList.contains('alive'))
								friends++;
						}
						if (i + 1 < n && j - 1 >= 0)
						{
							if (elements[i + 1][j - 1].classList.contains('alive'))
								friends++;
						}
						if (i - 1 >= 0)
						{
							if (elements[i - 1][j].classList.contains('alive'))
								friends++;
						}
						if (i - 1 >= 0 && j + 1 < n)
						{
							if (elements[i - 1][j + 1].classList.contains('alive'))
								friends++;
						}
						if (j + 1 < n)
						{
							if (elements[i][j + 1].classList.contains('alive'))
								friends++;
						}
						if (i + 1 < n)
						{
							if (elements[i + 1][j].classList.contains('alive'))
								friends++;
						}
						if (i + 1 < n && j + 1 < n)
						{
							if (elements[i + 1][j + 1].classList.contains('alive'))
								friends++;
						}
						if (friends == 3)
							elements[i][j].classList.add('can_born');
					}
				}
			}
			for (i = 0; i < n; i++)
			{
				for (j = 0; j < n; j++)
				{
					if (elements[i][j].classList.contains('can_die'))
					{
						elements[i][j].classList.remove('can_die');
						elements[i][j].classList.remove('alive');
						elements[i][j].classList.add('dead');
					}
					else if (elements[i][j].classList.contains('can_born'))
					{
						elements[i][j].classList.remove('can_born');
						elements[i][j].classList.remove('dead');
						elements[i][j].classList.add('alive');
					}
				}
			}
		}
		function stay_alive()
		{
			for (i = 0; i < n; i++)
			{
				for (j = 0; j < n; j++)
				{
					if (elements[i][j].classList.contains('alive'))
						return true;
				}
			}
			return false;
		}
		function chacha()
		{
			if (currentInterval === null)
			{
				currentInterval = window.setInterval(function(ev) {
				if (stay_alive())
					next_gen();
				}, 1000);
			}
			else
			{
				window.clearInterval(currentInterval);
				currentInterval = null;
			}
		}
	</script>
</body>
</html>
