<?php

namespace App\Http\Resources\CMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CMSResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // metadata parse
        $metadata = [];
        if (!empty($this->metadata)) {
            $metadata = is_array($this->metadata)
                ? $this->metadata
                : json_decode($this->metadata, true) ?? [];
        }

        // gallery images
        $galleryImages = [];
        if (!empty($metadata['images']) && is_array($metadata['images'])) {
            $galleryImages = array_map(fn($path) => asset($path), $metadata['images']);
        }

        // video — full URL
        $videoUrl = null;
        if (!empty($metadata['video'])) {
            $videoUrl = asset($metadata['video']);
        }

        return [
            'id'              => $this->id,
            'page'            => $this->page,
            'section'         => $this->section,
            'title'           => $this->title,
            'sub_title'       => $this->when(
                $this->sub_title && $this->sub_title !== 'null',
                $this->sub_title
            ),
            'description'     => $this->when($this->description, $this->description),
            'sub_description' => $this->when($this->sub_description, $this->sub_description),
            'image'           => $this->when(
                $this->image && $this->image !== '',
                asset($this->image)
            ),
            'btn_text'        => $this->when($this->btn_text, $this->btn_text),

            // metadata
            'gallery_images'  => $this->when(!empty($galleryImages), $galleryImages),
            'video'           => $this->when(!empty($videoUrl), $videoUrl),
        ];
    }
}
