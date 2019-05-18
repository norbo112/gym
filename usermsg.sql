-- phpMyAdmin SQL Dump
-- version 4.8.0.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2018. Júl 30. 00:46
-- Kiszolgáló verziója: 10.1.32-MariaDB
-- PHP verzió: 7.2.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `edzesnaplo`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `usermsg`
--

CREATE TABLE `usermsg` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `felhki` varchar(255) COLLATE utf8_hungarian_ci NOT NULL,
  `felhkitol` varchar(255) COLLATE utf8_hungarian_ci NOT NULL,
  `letrehozas` datetime NOT NULL,
  `msgtype` varchar(50) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `msgcim` varchar(150) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `uzenet` text COLLATE utf8_hungarian_ci NOT NULL,
  `olvasva` tinyint(4) NOT NULL DEFAULT '0',
  `valaszvolt` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `usermsg`
--

INSERT INTO `usermsg` (`id`, `felhki`, `felhkitol`, `letrehozas`, `msgtype`, `msgcim`, `uzenet`, `olvasva`, `valaszvolt`) VALUES
(1, 'tg.sures@gmail.com', 'peter.igloi@gmail.com', '2018-07-25 14:24:58', 'Webalkalmazás', 'Teszt Üzenet', 'Na vajon be kerül a hír az adminoknak? Még megkell irnom azt is hogy meglehessen jeleníteni, majd fönt balra a kis jelzés is valóst mutasson', 1, 0),
(2, 'jnorbo@gmail.com', 'peter.igloi@gmail.com', '2018-07-25 14:24:58', 'Webalkalmazás', 'Teszt Üzenet', 'Na vajon be kerül a hír az adminoknak? Még megkell irnom azt is hogy meglehessen jeleníteni, majd fönt balra a kis jelzés is valóst mutasson', 1, 0),
(3, 'tg.sures@gmail.com', 'test@test.com', '2018-07-25 17:34:57', 'Fejlesztés', 'Kérek képet', 'Szeretném ha lenne az adataim fölött saját kép', 1, 0),
(4, 'jnorbo@gmail.com', 'test@test.com', '2018-07-25 17:34:57', 'Fejlesztés', 'Kérek képet', 'Szeretném ha lenne az adataim fölött saját kép', 1, 0),
(7, 'test@test.com', 'tg.sures@gmail.com', '2018-07-26 16:05:54', NULL, 'Hello', 'Dolgozom a megoldáson és köszönöm az észrevételt', 1, 0),
(8, 'test@test.com', 'tg.sures@gmail.com', '2018-07-26 20:14:04', NULL, 'Hello', 'Proba', 1, 0),
(11, 'tg.sures@gmail.com', 'test@test.com', '2018-07-27 13:42:38', NULL, 'Ismét én', 'Kérlek segíts', 1, 0),
(13, 'test@test.com', 'tg.sures@gmail.com', '2018-07-27 13:56:20', NULL, 'Próba', 'Próbálok mindent amit lehet', 1, 0),
(15, 'test@test.com', 'tg.sures@gmail.com', '2018-07-27 14:06:57', NULL, 'Hello', 'Oké rendben', 1, 0),
(16, 'test@test.com', 'tg.sures@gmail.com', '2018-07-27 14:12:50', NULL, 'teszt ütenet', 'Vűéasz egykettőhárom', 1, 0),
(20, 'test@test.com', 'tg.sures@gmail.com', '2018-07-27 14:31:55', NULL, 'Hello', 'Még valamit?', 1, 0),
(31, 'peter.igloi@gmail.com', 'tg.sures@gmail.com', '2018-07-27 17:20:57', NULL, 'Hello', 'Dolgozom a név megjelenítésén', 1, 0),
(32, 'peter.igloi@gmail.com', 'tg.sures@gmail.com', '2018-07-27 21:55:38', NULL, 'Hello', 'Tesztelem a mobilt', 1, 0),
(34, 'jnorbo@gmail.com', 'peter.igloi@gmail.com', '2018-07-28 23:15:47', NULL, 'Helló bello', 'Ismét egy kis teszt az üzenetekhez', 1, 0),
(35, 'peter.igloi@gmail.com', 'jnorbo@gmail.com', '2018-07-28 23:16:50', NULL, 'Kösz', 'Köszi köszi köszi', 1, 0);

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `usermsg`
--
ALTER TABLE `usermsg`
  ADD UNIQUE KEY `id` (`id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `usermsg`
--
ALTER TABLE `usermsg`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
