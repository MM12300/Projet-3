-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 09 mai 2020 à 15:25
-- Version du serveur :  10.4.11-MariaDB
-- Version de PHP : 7.4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `Projet-3`
--

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `users_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `articles`
--

INSERT INTO `articles` (`id`, `title`, `content`, `featured_image`, `created_at`, `users_id`) VALUES
(1, 'Les rois du Curling', '<p>Cet été on a ajouté une super activité au catalogue d’activités pour nos visiteurs : le curling. On y est allés quatre fois en tout, et ce qu’on peut dire avec certitude, c’est que tout le monde a super adoré.</p>\r\n<p>Le curling, ce n’est pas vraiment une institution en Norvège, mais l’équipe nationale a quelque chose d’assez singulier : ses pantalons et son capitaine qui se la pète. Si vous avez l’occasion de regarder un match de curling de la Norvège (entre deux matchs de snooker sur Eurosport2), vous le verrez peut-être faire un bisou à la caméra.</p>\r\n<p>Si vous n’avez pas Eurosport2, voilà une publicité pour des caleçons avec l’équipe (la marque s’appelle “comfyballs”, soit “coquilles à l’aise” pour bien parler… classe!) :</p>\r\n<p><iframe src=\"https://www.youtube.com/embed/1USbTGYv9ZM?feature=oembed\" allowfullscreen=\"\" width=\"640\" height=\"360\" frameborder=\"0\"></iframe></p>\r\n<p>(Oui il y a un gros logo Renault sur les t-shirt, le Radisson Blu en arrière-plan et des basket Nike, c’est parce que les norvégiens sont les rois des publicités incluant d’autres marques. Par exemple il y a une pub pour des légumes cuisinés, on pourrait penser que c’est une pub pour Apple).</p>\r\n<p>Si vous visitez Bergen, vous pouvez profiter de l’occasion pour faire une petite séance coachée (en anglais si besoin) à <a href=\"https://vannkanten.keysecure.no/booking/curling/?__sessionid=49dnt3f3aav4mha1rqpajk50t2\">Vestkanten</a>, accessible en bus depuis le centre-ville. Ca coûte 100 kr/personne/h, ou bien 250 kr/personne pour 2h dont 30 min avec le coach (en ce moment 1€~8.8 kr). Le système de chaussures est inclus dans le prix.</p>\r\n<p>Sinon, on a vu qu’il y avait une piste à Lyon, et dans quelques autres villes de France.</p>\r\n<p>Le curling se joue un peu comme la pétanque, si ce n’est que la piste est glacée et très longue. On “lance” des pierres d’une vingtaine de kilos, mais comme on fait glisser tout ca, il n’y a aucun risque de se faire mal au bras ou aux doigts (contrairement au bowling. Vous la voyez la reine du bowling là?).</p>\r\n<p>Au bout de la piste, il y a une cible et le but est de placer le plus de pierres près du coeur de la cible. Si on n’atteint pas ou si on dépasse le carré entourant la cible, la pierre n’est pas valide.</p>\r\n<p><img class=\"alignnone\" src=\"https://farm8.staticflickr.com/7313/16366023945_9d467449e3_c.jpg\" alt=\"\" width=\"534\" height=\"800\"></p>\r\n<p><img class=\"alignnone\" src=\"https://farm9.staticflickr.com/8676/16365125172_296770b37d_c.jpg\" alt=\"\" width=\"800\" height=\"534\"></p>\r\n<p>Chaque équipe à 8 pierres. Chaque joueur en lance 2 en alternance avec l’autre équipe (donc 1 bleu, 1 rouge, 1 bleu, 1 rouge, puis on change de lanceur). Les autres membres de l’équipe sont sur la piste et balaient. Balayer permet de lisser la glace, donc de supprimer des frottements : la pierre peut ainsi gagner 3 mètres. La technique consiste donc à lancer un peu trop juste, et d’ajuster en balayant. Enfin quand on est pro, parce que nous on lance où on peut et ensuite les autres se démerdent.</p>\r\n<p>Les points se comptent comme à la pétanque : le nombre de pierres le plus proche du centre jusqu’à tomber sur une pierre adverse.</p>\r\n<p>Ceux qui gagnent le point commencent ensuite à lancer. Mais comme il est plus stratégique de finir la série de lancer, les équipes préfèrent parfois ne marquer aucun point plutôt qu’un seul (par contre, elles ne cracheront pas sur 2 points), histoire de finir de lancer au tour suivant. Bon nous on est nuls, alors chaque point est un point.</p>\r\n<p><img class=\"alignnone\" src=\"https://farm8.staticflickr.com/7284/16179809279_80513c810f_c.jpg\" alt=\"\" width=\"800\" height=\"534\"></p>\r\n<p><img class=\"alignnone\" src=\"https://farm8.staticflickr.com/7445/16180139657_3278900118_c.jpg\" alt=\"\" width=\"800\" height=\"534\"></p>\r\n<p>Pour se déplacer sur la glace, on a deux chaussures différentes : l’une glisse (c’est plus pratique pour lancer) tandis qu’une autre tient bien à la glace et vous permet à peu près de marcher voire même de glisser avec grâce. Enfin sauf quand on se viande.</p>\r\n<p>On peut soit mettre des sur-chaussures (pratique et inclus dans le prix), soit avoir des chaussures super-sexy (ici parce que les sur-chaussures étaient tous abimés en fin de saison) :</p>\r\n<p><img class=\"alignnone\" src=\"https://farm9.staticflickr.com/8626/16178399308_a56db15a8f_c.jpg\" alt=\"\" width=\"800\" height=\"534\"></p>\r\n<p>Grâce à la partie glissante, on peut s’étendre pour lancer la pierre sur la glace tout en maintenant son équilibre avec le balai savamment positionné. Bon moi je m’étends tellement que je finis la moitié du temps avec les fesses sur la glace. Les vrais, ils gèrent un peu mieux que moi :</p>\r\n<p><img class=\"alignnone\" src=\"https://farm8.staticflickr.com/7383/16180139297_21f4d05c5d_c.jpg\" alt=\"\" width=\"800\" height=\"534\"></p>\r\n<p>(oui j’ai un pull rose et j’adore avoir le choix entre du rose et du violet, super, merci!)</p>\r\n<p><img class=\"alignnone\" src=\"https://upload.wikimedia.org/wikipedia/commons/f/fc/Martin_Sesaker_at_the_2012_Youth_Winter_Olympics.jpg\" alt=\"\" width=\"821\" height=\"545\"></p>\r\n<p>(<a href=\"http://en.wikipedia.org/wiki/Curling\">source wikipedia</a>)</p>\r\n<p>Le secret de la réussite, c’est d’accompagner la pierre puis de la laisser partir seule sur le chemin de la vie. À la fin de l’accompagnement, on peut la mettre légèrement en rotation pour la maintenir sur la ligne droite qu’on vise.</p>\r\n<p>Dans les faits, la première fois, on se pousse comme on peut et à la fin on donne un grand coup avec le bras pour être sur la pierre s’en aille. Alors elle part n’importe où et sort des lignes. Ensuite on se relève, on glisse péniblement en se crispant sur le balai pour rester debout mais finalement on se retrouve les quatre fers en l’air. Fuck yeah le curling!</p>\r\n<p>L’autre secret est un secret de balayeur : il faut balayer devant la pierre.</p>\r\n<p>Ouais, rigolez bien, l’instructeur nous a dit que certains balayaient derrière. Bref, le vrai secret, c’est le balayer devant la pierre, mais légèrement du côté où vous espérez la diriger : grâce à la différence de frottements vous pourrez induire une rotation. Je sais pas à quel point ça marche, mais j’y crois dur comme fer.</p>\r\n<p><img class=\"alignnone\" src=\"https://farm8.staticflickr.com/7419/16340054036_afeb8e34e4_c.jpg\" alt=\"\" width=\"800\" height=\"534\"></p>\r\n<p>Alors, convaincus ?</p>\r\n<p>&nbsp;</p>', NULL, '2020-04-18 12:56:49', 1),
(2, 'Le «cheese rolling»', '<p>Ayant commencé en Angleterre, cette tradition fait beaucoup d’adeptes. Ce <strong>sport insolite</strong> est une course qui se déroule dans une colline et où l’on doit courir après un fromage rond de 4,1 kilogrammes. Le but est d’attraper le fromage, mais comme le celui-ci peut atteindre une vitesse d’environ 110km/heure, le but le plus réaliste est d’être la personne arrivant au bas de la colline en premier. Par contre, ce sport cause beaucoup de blessures, alors il faut l’exercer avec précautions!</span></p>\r\n<p><a href=\"https://www.youtube.com/watch?v=fWpkDDDj1QM\"><span style=\"font-weight: 400\">Voir une vidéo</span></a></p>', NULL, '2020-04-18 12:58:39', 2),
(3, 'La course de chaise de bureau', '<p><span style=\"font-weight: 400\">Cette course, se déroulant en Allemagne, est simple: dévaler une pente de 200 mètres de long dans votre chaise de bureau favorite. De plus, vous pouvez gagner des points pour votre originalité, alors les participants sont invités à se surpasser pour impressionner les spectateurs. C’est une activité parfaite pour les journées mornes au travail!</span></p>\r\n<p><a href=\"https://www.youtube.com/watch?v=CvZIsL90hjM&amp;feature=youtu.be\"><span style=\"font-weight: 400\">Voir une vidéo</span></a></p>', NULL, '2020-04-18 12:59:50', 1),
(4, 'Aya Nakamura victime d’un AVC en essayant d’écrire une phrase qui a du sens', '<div><img src=\"https://cdn.nordpresse.be/wp-content/uploads/2019/11/photo_1545298483.png\" alt=\"\" title=\"Aya Nakamura\" width=\"574\" height=\"322\"></div>\r\n<p>La chanteuse (sic) à succès a été transportée d’urgence au Centre hospitalier intercommunal Robert Ballanger ce dimanche des suites d’un AVC. Ses deux sœurs, présentes lors des faits, nous racontent.</p>\r\n<p>«&nbsp;Elle nous a dit qu’elle avait eu une idée lumineuse, qu’elle allait révolutionner le monde de la musique, encore une fois&nbsp;», nous dit Amy, la plus âgée.</p>\r\n<p>«&nbsp;Elle s’est enfermée dans la cuisine et on l’a laissée tranquille pour que son talent puisse s’exprimer. Nous nous sommes cependant vite inquiétées quand nous avons entendu des mots intelligibles sortir de sa bouche. Nous avons tout de suite compris que quelque chose d’anormal était entrain de se produire.&nbsp;», nous raconte la plus jeune ; avant de fondre en larmes (sa grande sœur la console).</p>\r\n<p>Amy reprend : «&nbsp;J’ai tout de suite appelé une ambulance et j’ai essayé de lui porter secours. Mais il était trop tard elle était dans un état second : elle ne cessait de répéter «&nbsp;Re re re renard&nbsp;». Je lui ai donc pris son iPhone des mains et ce que j’y ai lu m’a glacé le sang.&nbsp;». A cet instant, Amy a un haut le cœur en tournant l’iPhone d’Aya vers nous. Nous constatons avec effroi ce qu’Aya a écrit : «&nbsp;Le renard est un animal&nbsp;».</p>\r\n<p>Aya est désormais dans un état stable et ne devrait avoir aucune séquelle mais elle s’en souviendra probablement toute sa vie tout comme ses sœurs, très touchées par cet épisode de leur vie. Nous n’avons encore aucune information quant à la date de sortie de cette nouvelle chanson mais cela risque de bouleverser toute la fanbase de cette icône de la musique française (sic).</p>', NULL, '2020-04-18 13:03:43', 2);

-- --------------------------------------------------------

--
-- Structure de la table `articles_categories`
--

CREATE TABLE `articles_categories` (
  `articles_id` int(11) NOT NULL,
  `categories_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `articles_categories`
--

INSERT INTO `articles_categories` (`articles_id`, `categories_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(3, 2),
(4, 2);

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Sport'),
(2, 'Actualités'),
(6, 'Robertino');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '["ROLE_USER"]' CHECK (json_valid(`roles`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `remember_token`, `roles`) VALUES
(1, 'contact@demo.fr', '$2y$10$REc3LRtrcHHUJgT2UsfYVuXC5kNaVznLs3ByBl3zRe79zwgdw1.8e', '', NULL, '[\"ROLE_USER\"]'),
(2, 'autre@demo.fr', '$2y$10$ynkm.SsCM0VdYAl9rmwwXuo1E28ConjgoxidfA0bQ7SE5HoTE0ZUu', '', NULL, '[\"ROLE_USER\"]'),
(3, 'test@gmail.com', '$2y$10$7kJ.c5mTSbchzpFExEDlDuTHr6xRqdaS4yyXBe6eLgMfgAakgXMU6', 'James', NULL, '[\"ROLE_USER\"]'),
(4, 'test2@gmail.com', '$2y$10$/3h4mefkcX0pndA33.NGEuASwCw6CQlPh1v864almGN6POSwv/aAy', 'admin2', NULL, '[\"ROLE_USER\"]'),
(6, 'etoilenoire@gmail.com', '$2y$10$lAeNSQCRCT9KS3Xkl66XSe6sTdZbd4gAdEUDJZen6wX4fy2VX2MVi', 'user123456', '9258ffe835e756678d12d7a0f8658ae4', '[\"ROLE_USER\",\"ROLE_ADMIN\"]');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`users_id`);

--
-- Index pour la table `articles_categories`
--
ALTER TABLE `articles_categories`
  ADD PRIMARY KEY (`articles_id`,`categories_id`),
  ADD KEY `articles_id` (`articles_id`,`categories_id`),
  ADD KEY `categories_id` (`categories_id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `articles_categories`
--
ALTER TABLE `articles_categories`
  ADD CONSTRAINT `articles_categories_ibfk_1` FOREIGN KEY (`articles_id`) REFERENCES `articles` (`id`),
  ADD CONSTRAINT `articles_categories_ibfk_2` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
