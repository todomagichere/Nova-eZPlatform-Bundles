<?php

/**
 * NovaeZSlackBundle Bundle.
 *
 * @package   Novactive\Bundle\eZSlackBundle
 *
 * @author    Novactive <s.morel@novactive.com>
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaeZSlackBundle/blob/master/LICENSE MIT Licence
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZSlackBundle\Core\Decorator;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content as ValueContent;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use eZ\Publish\Core\FieldType\RelationList\Value as RelationListValue;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Novactive\Bundle\eZSlackBundle\Core\Slack\Attachment as AttachmentModel;
use Novactive\Bundle\eZSlackBundle\Core\Slack\Author;

/**
 * Class Attachment.
 */
class Attachment
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    /**
     * Attachment constructor.
     */
    public function __construct(Repository $repository, ConfigResolverInterface $configResolver)
    {
        $this->repository = $repository;
        $this->configResolver = $configResolver;
    }

    public function addAuthor(AttachmentModel $attachment, int $authorId): void
    {
        $attachment->setAuthor($this->getAuthor($authorId));
    }

    public function addSiteInformation(AttachmentModel $attachment): void
    {
        $attachment->setFooter($this->getParameter('site_name'));
        $attachment->setFooterIcon($this->getParameter('favicon'));
    }

    public function decorate(AttachmentModel $attachment, ?string $type = null): void
    {
        $attachment->setTitle($this->sanitize($attachment->getTitle()));
        $attachment->setText($this->sanitize($attachment->getText()));

        $styles = $this->getParameter('styles');
        if (isset($styles['attachment'][$type])) {
            $attachment->setColor($styles['attachment'][$type]);
        }

        $attachment->setFallback($attachment->getTitle());
        if ('' === $attachment->getFallback()) {
            $attachment->setFallback($this->sanitize($attachment->getText()));
        }
    }

    /**
     * @param $name
     */
    private function getParameter($name)
    {
        return $this->configResolver->getParameter($name, 'nova_ezslack');
    }

    private function getAuthor(int $contentId): Author
    {
        return $this->repository->sudo(
            function (Repository $repository) use ($contentId) {
                $contentService = $repository->getContentService();
                $owner = $contentService->loadContent($contentId);
                $author = new Author($this->sanitize($owner->contentInfo->name));
                $author->setIcon($this->getPictureUrl($owner));

                return $author;
            }
        );
    }

    /**
     * @return string
     */
    private function sanitize(?string $text): ?string
    {
        if (null === $text) {
            return null;
        }

        return trim(strip_tags(html_entity_decode($text)));
    }

    public function getPictureUrl(ValueContent $content): ?string
    {
        $fieldIdentifiers = $this->getParameter('field_identifiers')['image'];
        foreach ($fieldIdentifiers as $try) {
            $value = $content->getFieldValue($try);
            if ($value instanceof ImageValue) {
                return ($this->getParameter('asset_prefix') ?? '').$value->uri;
            }
            if ($value instanceof RelationListValue && \count($value->destinationContentIds) > 0) {
                $image = $this->repository->getContentService()->loadContent($value->destinationContentIds[0]);

                return $this->getPictureUrl($image);
            }
        }

        return null;
    }
}
